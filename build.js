const esbuild = require('esbuild');
const sass = require('sass');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const isWatch = process.argv.includes('--watch');
const srcDir = path.join(__dirname, 'system');
const distDir = path.join(__dirname, 'dist');

// Modules: common (shared) + graph + filter (specific)
const modules = [
    { name: 'common', scss: 'shared.scss', js: 'common.js' },
    { name: 'graph', scss: 'graph.scss', js: 'graph.js' },
    { name: 'filter', scss: 'filter.scss', js: 'filter.js' }
];

if (!fs.existsSync(distDir)) fs.mkdirSync(distDir, { recursive: true });

function generateHash(content) {
    return crypto.createHash('md5').update(content).digest('hex').substring(0, 8);
}

function cleanDist() {
    fs.readdirSync(distDir).forEach(file => {
        if (file !== 'manifest.json') fs.unlinkSync(path.join(distDir, file));
    });
}

function compileSass(scssFile, name) {
    try {
        const scssPath = path.join(srcDir, `styles/src/${scssFile}`);
        if (!fs.existsSync(scssPath)) return null;

        const result = sass.compile(scssPath, { style: 'compressed', sourceMap: false });
        const hash = generateHash(result.css);
        const filename = `${name}.${hash}.css`;
        fs.writeFileSync(path.join(distDir, filename), result.css);
        console.log(`CSS: ${filename}`);
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

        const result = await esbuild.build({
            entryPoints: [jsPath], bundle: true, minify: true, sourcemap: false,
            write: false, target: ['es2015'], format: 'iife',
            globalName: `${name.charAt(0).toUpperCase() + name.slice(1).replace(/_/g, '')}App`
        });

        const content = result.outputFiles[0].text;
        const hash = generateHash(content);
        const filename = `${name}.${hash}.js`;
        fs.writeFileSync(path.join(distDir, filename), content);
        console.log(`JS: ${filename}`);
        return filename;
    } catch (error) {
        console.error(`JS Error (${name}):`, error.message);
        return null;
    }
}

function writeManifest(assets) {
    fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify({ ...assets, timestamp: new Date().toISOString() }, null, 2));
    console.log('Manifest updated');
}

async function build() {
    console.log('Building...\n');
    cleanDist();
    const assets = {};

    for (const mod of modules) {
        const cssFile = compileSass(mod.scss, mod.name);
        const jsFile = await bundleJs(mod.js, mod.name);
        if (cssFile) assets[`${mod.name}_css`] = cssFile;
        if (jsFile) assets[`${mod.name}_js`] = jsFile;
    }

    writeManifest(assets);
    console.log('\nBuild complete!');
}

async function watch() {
    console.log('Starting watch mode...\n');
    await build();

    fs.watch(path.join(srcDir, 'styles/src'), { recursive: true }, async (_, filename) => {
        if (filename?.endsWith('.scss')) {
            console.log(`\nSCSS changed: ${filename}`);
            const manifest = JSON.parse(fs.readFileSync(path.join(distDir, 'manifest.json')));
            for (const mod of modules) {
                const cssFile = compileSass(mod.scss, mod.name);
                if (cssFile) manifest[`${mod.name}_css`] = cssFile;
            }
            manifest.timestamp = new Date().toISOString();
            fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
        }
    });

    fs.watch(path.join(srcDir, 'scripts/src'), { recursive: true }, async (_, filename) => {
        if (filename?.endsWith('.js')) {
            console.log(`\nJS changed: ${filename}`);
            const manifest = JSON.parse(fs.readFileSync(path.join(distDir, 'manifest.json')));
            for (const mod of modules) {
                const jsFile = await bundleJs(mod.js, mod.name);
                if (jsFile) manifest[`${mod.name}_js`] = jsFile;
            }
            manifest.timestamp = new Date().toISOString();
            fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
        }
    });

    console.log('Watching for changes...');
}

if (isWatch) watch(); else build();
