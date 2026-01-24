const esbuild = require("esbuild");
const sass = require("sass");
const postcss = require("postcss");
const autoprefixer = require("autoprefixer");
const prefixSelector = require("postcss-prefix-selector");
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

// Modules: common (shared) + graph + counter + table + data-filter + dashboard (specific)
const modules = [
  { name: "common", scss: "shared.scss", js: "common.js" },
  { name: "graph", scss: "graph.scss", js: "graph.js" },
  { name: "counter", scss: "counter.scss", js: "counter.js" },
  { name: "table", scss: "table.scss", js: "table.js" },
  { name: "data-filter", scss: "data-filter.scss", js: "data-filter.js" },
  { name: "dashboard", scss: "dashboard.scss", js: "dashboard.js" },
];

// Per-page scripts (not bundled, just minified with cache-busting hash)
const pageScripts = [
  { module: "graph", scripts: ["graph-list", "graph-creator"] },
  { module: "counter", scripts: ["counter-list", "counter-creator"] },
  { module: "table", scripts: ["table-list", "table-creator"] },
  {
    module: "dashboard",
    scripts: [
      "dashboard-list",
      "dashboard-builder",
      "dashboard-preview",
      "template-list",
      "template-editor",
      "template-builder",
      "template-preview",
    ],
  },
  { module: "data-filter", scripts: ["data-filter-list"] },
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
    const filePath = path.join(distDir, file);
    // Skip manifest.json and per_page directory (handled by cleanDistPerPage)
    if (file === "manifest.json" || file === "per_page") return;
    if (fs.statSync(filePath).isDirectory()) {
      fs.rmSync(filePath, { recursive: true, force: true });
    } else {
      fs.unlinkSync(filePath);
    }
  });
}

/**
 * Clean old versions of a specific module's files
 * @param {string} name - Module name (e.g., 'graph', 'common')
 * @param {string} type - File type ('css' or 'js')
 */
function cleanOldVersions(name, type) {
  const pattern = new RegExp(`^${name}\\.[a-f0-9]{8}\\.${type}(\\.map)?$`);
  fs.readdirSync(distDir).forEach((file) => {
    if (pattern.test(file)) {
      fs.unlinkSync(path.join(distDir, file));
    }
  });
}

/**
 * Clean old versions of a specific per-page script
 * @param {string} module - Module name (e.g., 'graph', 'dashboard')
 * @param {string} scriptName - Script name (e.g., 'graph-list')
 */
function cleanOldPageVersions(module, scriptName) {
  const moduleDir = path.join(distDir, "per_page", module);
  if (!fs.existsSync(moduleDir)) return;

  const pattern = new RegExp(`^${scriptName}\\.[a-f0-9]{8}\\.js(\\.map)?$`);
  fs.readdirSync(moduleDir).forEach((file) => {
    if (pattern.test(file)) {
      fs.unlinkSync(path.join(moduleDir, file));
    }
  });
}

/**
 * Clean the entire per_page directory
 */
function cleanDistPerPage() {
  const perPageDir = path.join(distDir, "per_page");
  if (fs.existsSync(perPageDir)) {
    fs.rmSync(perPageDir, { recursive: true, force: true });
  }
}

/**
 * Bundle a per-page script (minify only, no bundling since they use global deps)
 * @param {string} module - Module name (e.g., 'graph', 'dashboard')
 * @param {string} scriptName - Script name without extension (e.g., 'graph-list')
 * @returns {Promise<string|null>} Relative path for manifest or null on error
 */
async function bundlePageScript(module, scriptName) {
  try {
    const jsPath = path.join(srcDir, `scripts/${module}/${scriptName}.js`);
    if (!fs.existsSync(jsPath)) {
      console.warn(`Page script not found: ${module}/${scriptName}.js`);
      return null;
    }

    const generateSourceMap = isDev || isWatch;

    // Use esbuild for minification only (bundle: false)
    const result = await esbuild.build({
      entryPoints: [jsPath],
      bundle: false, // No bundling - page scripts use global dependencies
      minify: isProd,
      sourcemap: generateSourceMap ? "inline" : false,
      write: false,
      target: ["es2015"],
    });

    let content = result.outputFiles[0].text;
    let sourceMap = null;

    // Extract inline sourcemap for dev mode
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

    const hash = generateHash(content);
    const filename = `${scriptName}.${hash}.js`;
    const mapFilename = `${scriptName}.${hash}.js.map`;

    // Ensure module directory exists
    const moduleDir = path.join(distDir, "per_page", module);
    if (!fs.existsSync(moduleDir)) {
      fs.mkdirSync(moduleDir, { recursive: true });
    }

    // Write JS with sourcemap reference for dev mode
    if (generateSourceMap && sourceMap) {
      content += `//# sourceMappingURL=${mapFilename}`;
      fs.writeFileSync(path.join(moduleDir, mapFilename), sourceMap);
    }

    fs.writeFileSync(path.join(moduleDir, filename), content);
    console.log(
      `Page: per_page/${module}/${filename}${
        generateSourceMap ? " + " + mapFilename : ""
      }`
    );

    return `per_page/${module}/${filename}`;
  } catch (error) {
    console.error(`Page Script Error (${module}/${scriptName}):`, error.message);
    return null;
  }
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

    // Add vendor prefixes with autoprefixer and scope all selectors under .dgc-app
    // This prevents CSS conflicts with Rapidkart's Bootstrap 3 styles
    const prefixed = await postcss([
      prefixSelector({
        prefix: ".dgc-app",
        // Don't prefix these selectors
        exclude: [
          ":root",           // CSS variables must stay at :root
          ":root.theme-dark", // Theme dark mode
          ":root.theme-light", // Theme light mode
          /^\[data-theme/, // data-theme attributes
          "@keyframes",      // Keyframe animations
          "@font-face",      // Font declarations
        ],
        // Transform rules
        transform: function (prefix, selector, prefixedSelector) {
          // Don't prefix html or body - they're global
          if (selector === "html" || selector === "body") {
            return selector;
          }
          // Don't prefix * selector
          if (selector === "*") {
            return selector;
          }
          // Handle :root properly - keep as is
          if (selector.startsWith(":root")) {
            return selector;
          }
          // Don't prefix .dgc-app itself - it's on body element
          if (selector.startsWith(".dgc-app")) {
            return selector;
          }
          // Don't prefix Rapidkart layout elements - they're outside .dgc-app wrapper
          if (selector.startsWith(".navbar") || selector.startsWith("#sidebar") || selector.startsWith("#main-content")) {
            return selector;
          }
          return prefixedSelector;
        },
      }),
      autoprefixer,
    ]).process(result.css, {
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
        name.split(/[-_]/).map(part => part.charAt(0).toUpperCase() + part.slice(1)).join('')
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
  cleanDistPerPage();
  const assets = {};

  // Build module bundles (CSS + JS)
  for (const mod of modules) {
    const cssFile = await compileSass(mod.scss, mod.name);
    const jsFile = await bundleJs(mod.js, mod.name);
    if (cssFile) assets[`${mod.name}_css`] = cssFile;
    if (jsFile) assets[`${mod.name}_js`] = jsFile;
  }

  // Build per-page scripts
  console.log("\nBuilding per-page scripts...");
  for (const pageConfig of pageScripts) {
    for (const scriptName of pageConfig.scripts) {
      const pageFile = await bundlePageScript(pageConfig.module, scriptName);
      if (pageFile) {
        // Manifest key: page_{module}_{scriptName} with dashes converted to underscores
        const moduleKey = pageConfig.module.replace(/-/g, "_");
        const scriptKey = scriptName.replace(/-/g, "_");
        assets[`page_${moduleKey}_${scriptKey}`] = pageFile;
      }
    }
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
        cleanOldVersions(mod.name, "css"); // Clean old CSS versions
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
        cleanOldVersions(mod.name, "js"); // Clean old JS versions
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

  // Rebuild a single per-page script
  const rebuildPageScript = debounce(async (module, scriptName) => {
    if (isRebuilding) return;
    isRebuilding = true;
    try {
      console.log(`\nRebuilding page script: ${module}/${scriptName}...`);
      const manifest = JSON.parse(
        fs.readFileSync(path.join(distDir, "manifest.json"))
      );

      // Clean old version of this specific script
      cleanOldPageVersions(module, scriptName);

      const pageFile = await bundlePageScript(module, scriptName);
      if (pageFile) {
        const moduleKey = module.replace(/-/g, "_");
        const scriptKey = scriptName.replace(/-/g, "_");
        manifest[`page_${moduleKey}_${scriptKey}`] = pageFile;
      }

      manifest.timestamp = new Date().toISOString();
      fs.writeFileSync(
        path.join(distDir, "manifest.json"),
        JSON.stringify(manifest, null, 2)
      );
      console.log("Page script rebuild complete!");
    } catch (error) {
      console.error("Page script rebuild error:", error.message);
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

  // Watch per-page scripts (graph/, dashboard/, data-filter/ folders)
  const pageScriptPaths = pageScripts.map((p) =>
    path.join(srcDir, `scripts/${p.module}`)
  );
  const pageScriptWatcher = chokidar.watch(pageScriptPaths, {
    ignoreInitial: true,
    usePolling: true,
    interval: 300,
    ignored: /(^|[\/\\])\../,
  });

  pageScriptWatcher
    .on("change", (filePath) => {
      if (filePath.endsWith(".js")) {
        // Extract module and script name from path
        const relativePath = path.relative(
          path.join(srcDir, "scripts"),
          filePath
        );
        const parts = relativePath.split(path.sep);
        if (parts.length === 2) {
          const module = parts[0];
          const scriptName = path.basename(parts[1], ".js");
          console.log(`\nPage script changed: ${module}/${scriptName}.js`);
          rebuildPageScript(module, scriptName);
        }
      }
    })
    .on("add", (filePath) => {
      if (filePath.endsWith(".js")) {
        const relativePath = path.relative(
          path.join(srcDir, "scripts"),
          filePath
        );
        const parts = relativePath.split(path.sep);
        if (parts.length === 2) {
          const module = parts[0];
          const scriptName = path.basename(parts[1], ".js");
          console.log(`\nPage script added: ${module}/${scriptName}.js`);
          rebuildPageScript(module, scriptName);
        }
      }
    })
    .on("error", (error) => {
      console.error("Page script watcher error:", error.message);
    })
    .on("ready", () => {
      console.log("Page script watcher ready");
    });

  console.log("Watching for changes... (Press Ctrl+C to stop)\n");

  // Handle graceful shutdown
  process.on("SIGINT", () => {
    console.log("\nStopping watch mode...");
    scssWatcher.close();
    jsWatcher.close();
    pageScriptWatcher.close();
    process.exit(0);
  });
}

if (isWatch) watch();
else build();
