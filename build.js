const esbuild = require("esbuild");
const sass = require("sass");
const postcss = require("postcss");
const autoprefixer = require("autoprefixer");
const JavaScriptObfuscator = require("javascript-obfuscator");
const chokidar = require("chokidar");
const fs = require("fs");
const path = require("path");
const crypto = require("crypto");

const isWatch = process.argv.includes("--watch");
const isDev = process.argv.includes("--dev");
const isProd = !isWatch && !isDev; // Default build is production
const srcDir = path.join(__dirname, "system");
const distDir = path.join(__dirname, "dist");

// Modules: common (shared) + graph + filter + dashboard (specific)
const modules = [
  { name: "common", scss: "shared.scss", js: "common.js" },
  { name: "graph", scss: "graph.scss", js: "graph.js" },
  { name: "filter", scss: "filter.scss", js: "filter.js" },
  { name: "dashboard", scss: "dashboard.scss", js: "dashboard.js" },
];

// Obfuscator options for production
const obfuscatorOptions = {
  compact: true,
  controlFlowFlattening: true,
  controlFlowFlatteningThreshold: 0.5,
  deadCodeInjection: false,
  debugProtection: false,
  identifierNamesGenerator: "hexadecimal",
  renameGlobals: false,
  rotateStringArray: true,
  selfDefending: false,
  stringArray: true,
  stringArrayEncoding: ["base64"],
  stringArrayThreshold: 0.75,
  transformObjectKeys: true,
  unicodeEscapeSequence: false,
};

if (!fs.existsSync(distDir)) fs.mkdirSync(distDir, { recursive: true });

function generateHash(content) {
  return crypto.createHash("md5").update(content).digest("hex").substring(0, 8);
}

function cleanDist() {
  fs.readdirSync(distDir).forEach((file) => {
    if (file !== "manifest.json") fs.unlinkSync(path.join(distDir, file));
  });
}

async function compileSass(scssFile, name) {
  try {
    const scssPath = path.join(srcDir, `styles/src/${scssFile}`);
    if (!fs.existsSync(scssPath)) return null;

    const generateSourceMap = isDev || isWatch;
    const result = sass.compile(scssPath, {
      style: generateSourceMap ? "expanded" : "compressed",
      sourceMap: generateSourceMap,
      sourceMapIncludeSources: generateSourceMap,
    });

    // Add vendor prefixes with autoprefixer
    const prefixed = await postcss([autoprefixer]).process(result.css, {
      from: scssPath,
      map: generateSourceMap
        ? { prev: result.sourceMap, inline: false, annotation: false }
        : false,
    });

    const hash = generateHash(prefixed.css);
    const filename = `${name}.${hash}.css`;
    const mapFilename = `${name}.${hash}.css.map`;

    // Write CSS with sourcemap reference for dev mode
    let cssContent = prefixed.css;
    if (generateSourceMap) {
      cssContent += `\n/*# sourceMappingURL=${mapFilename} */`;
      fs.writeFileSync(path.join(distDir, mapFilename), prefixed.map.toString());
    }

    fs.writeFileSync(path.join(distDir, filename), cssContent);
    console.log(`CSS: ${filename}${generateSourceMap ? " + " + mapFilename : ""}`);
    return filename;
  } catch (error) {
    console.error(`SCSS Error (${name}):`, error.message);
    return null;
  }
}

async function bundleJs(jsFile, name) {
  try {
    const jsPath = path.join(srcDir, `scripts/src/${jsFile}`);
    if (!fs.existsSync(jsPath)) return null;

    const generateSourceMap = isDev || isWatch;

    // Dev/watch mode: no minification for easier debugging
    // Production: minify with esbuild first
    const result = await esbuild.build({
      entryPoints: [jsPath],
      bundle: true,
      minify: isProd,
      sourcemap: generateSourceMap ? "inline" : false,
      write: false,
      target: ["es2015"],
      format: "iife",
      globalName: `${
        name.charAt(0).toUpperCase() + name.slice(1).replace(/_/g, "")
      }App`,
    });

    let content = result.outputFiles[0].text;
    let sourceMap = null;

    // Extract inline sourcemap and make it external for dev mode
    if (generateSourceMap) {
      const sourceMapMatch = content.match(
        /\/\/# sourceMappingURL=data:application\/json;base64,(.+)$/
      );
      if (sourceMapMatch) {
        sourceMap = Buffer.from(sourceMapMatch[1], "base64").toString("utf8");
        content = content.replace(
          /\/\/# sourceMappingURL=data:application\/json;base64,.+$/,
          ""
        );
      }
    }

    // Production only: obfuscate the code
    if (isProd) {
      console.log(`  Obfuscating ${name}...`);
      const obfuscated = JavaScriptObfuscator.obfuscate(content, obfuscatorOptions);
      content = obfuscated.getObfuscatedCode();
    }

    const hash = generateHash(content);
    const filename = `${name}.${hash}.js`;
    const mapFilename = `${name}.${hash}.js.map`;

    // Write JS with sourcemap reference for dev mode
    if (generateSourceMap && sourceMap) {
      content += `//# sourceMappingURL=${mapFilename}`;
      fs.writeFileSync(path.join(distDir, mapFilename), sourceMap);
    }

    fs.writeFileSync(path.join(distDir, filename), content);
    console.log(`JS: ${filename}${generateSourceMap ? " + " + mapFilename : isProd ? " (obfuscated)" : ""}`);
    return filename;
  } catch (error) {
    console.error(`JS Error (${name}):`, error.message);
    return null;
  }
}

function writeManifest(assets) {
  fs.writeFileSync(
    path.join(distDir, "manifest.json"),
    JSON.stringify({ ...assets, timestamp: new Date().toISOString() }, null, 2)
  );
  console.log("Manifest updated");
}

async function build() {
  const mode = isProd ? "production" : "development";
  console.log(`Building (${mode})...\n`);
  cleanDist();
  const assets = {};

  for (const mod of modules) {
    const cssFile = await compileSass(mod.scss, mod.name);
    const jsFile = await bundleJs(mod.js, mod.name);
    if (cssFile) assets[`${mod.name}_css`] = cssFile;
    if (jsFile) assets[`${mod.name}_js`] = jsFile;
  }

  writeManifest(assets);
  console.log("\nBuild complete!");
}

// Debounce helper to prevent multiple rapid rebuilds
function debounce(fn, delay) {
  let timeout = null;
  return (...args) => {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => fn(...args), delay);
  };
}

async function watch() {
  console.log("Starting watch mode (development)...\n");
  await build();

  let isRebuilding = false;

  // Debounced rebuild functions
  const rebuildCss = debounce(async () => {
    if (isRebuilding) return;
    isRebuilding = true;
    try {
      console.log("\nRebuilding CSS...");
      const manifest = JSON.parse(
        fs.readFileSync(path.join(distDir, "manifest.json"))
      );
      for (const mod of modules) {
        const cssFile = await compileSass(mod.scss, mod.name);
        if (cssFile) manifest[`${mod.name}_css`] = cssFile;
      }
      manifest.timestamp = new Date().toISOString();
      fs.writeFileSync(
        path.join(distDir, "manifest.json"),
        JSON.stringify(manifest, null, 2)
      );
      console.log("CSS rebuild complete!");
    } catch (error) {
      console.error("CSS rebuild error:", error.message);
    } finally {
      isRebuilding = false;
    }
  }, 100);

  const rebuildJs = debounce(async () => {
    if (isRebuilding) return;
    isRebuilding = true;
    try {
      console.log("\nRebuilding JS...");
      const manifest = JSON.parse(
        fs.readFileSync(path.join(distDir, "manifest.json"))
      );
      for (const mod of modules) {
        const jsFile = await bundleJs(mod.js, mod.name);
        if (jsFile) manifest[`${mod.name}_js`] = jsFile;
      }
      manifest.timestamp = new Date().toISOString();
      fs.writeFileSync(
        path.join(distDir, "manifest.json"),
        JSON.stringify(manifest, null, 2)
      );
      console.log("JS rebuild complete!");
    } catch (error) {
      console.error("JS rebuild error:", error.message);
    } finally {
      isRebuilding = false;
    }
  }, 100);

  // Watch SCSS files with chokidar
  const scssPath = path.join(srcDir, "styles/src");
  const scssWatcher = chokidar.watch(scssPath, {
    ignoreInitial: true,
    usePolling: true,
    interval: 300,
    ignored: /(^|[\/\\])\../,
  });

  scssWatcher
    .on("change", (filePath) => {
      if (filePath.endsWith(".scss")) {
        console.log(`\nSCSS changed: ${path.basename(filePath)}`);
        rebuildCss();
      }
    })
    .on("add", (filePath) => {
      if (filePath.endsWith(".scss")) {
        console.log(`\nSCSS added: ${path.basename(filePath)}`);
        rebuildCss();
      }
    })
    .on("error", (error) => {
      console.error("SCSS watcher error:", error.message);
    })
    .on("ready", () => {
      console.log("SCSS watcher ready");
    });

  // Watch JS files with chokidar
  const jsPath = path.join(srcDir, "scripts/src");
  const jsWatcher = chokidar.watch(jsPath, {
    ignoreInitial: true,
    usePolling: true,
    interval: 300,
    ignored: /(^|[\/\\])\../,
  });

  jsWatcher
    .on("change", (filePath) => {
      if (filePath.endsWith(".js")) {
        console.log(`\nJS changed: ${path.basename(filePath)}`);
        rebuildJs();
      }
    })
    .on("add", (filePath) => {
      if (filePath.endsWith(".js")) {
        console.log(`\nJS added: ${path.basename(filePath)}`);
        rebuildJs();
      }
    })
    .on("error", (error) => {
      console.error("JS watcher error:", error.message);
    })
    .on("ready", () => {
      console.log("JS watcher ready");
    });

  console.log("Watching for changes... (Press Ctrl+C to stop)\n");

  // Handle graceful shutdown
  process.on("SIGINT", () => {
    console.log("\nStopping watch mode...");
    scssWatcher.close();
    jsWatcher.close();
    process.exit(0);
  });
}

if (isWatch) watch();
else build();
