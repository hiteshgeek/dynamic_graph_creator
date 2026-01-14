const esbuild = require('esbuild');
const sass = require('sass');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const isWatch = process.argv.includes('--watch');

const srcDir = path.join(__dirname, 'system');
const distDir = path.join(__dirname, 'dist');

// Ensure dist directory exists
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}

// Generate content hash for cache busting
function generateHash(content) {
    return crypto.createHash('md5').update(content).digest('hex').substring(0, 8);
}

// Clean old files from dist
function cleanDist() {
    const files = fs.readdirSync(distDir);
    files.forEach(file => {
        if (file !== 'manifest.json') {
            fs.unlinkSync(path.join(distDir, file));
        }
    });
}

// Compile SCSS
function compileSass() {
    try {
        const result = sass.compile(path.join(srcDir, 'styles/src/main.scss'), {
            style: 'compressed',
            sourceMap: false
        });

        const hash = generateHash(result.css);
        const filename = `app.${hash}.css`;

        fs.writeFileSync(path.join(distDir, filename), result.css);
        console.log(`CSS compiled: ${filename}`);

        return { css: filename };
    } catch (error) {
        console.error('SCSS Error:', error.message);
        return { css: null };
    }
}

// Bundle JavaScript
async function bundleJs() {
    try {
        const result = await esbuild.build({
            entryPoints: [path.join(srcDir, 'scripts/src/main.js')],
            bundle: true,
            minify: true,
            sourcemap: false,
            write: false,
            target: ['es2015'],
            format: 'iife',
            globalName: 'GraphCreatorApp'
        });

        const content = result.outputFiles[0].text;
        const hash = generateHash(content);
        const filename = `app.${hash}.js`;

        fs.writeFileSync(path.join(distDir, filename), content);
        console.log(`JS bundled: ${filename}`);

        return { js: filename };
    } catch (error) {
        console.error('JS Error:', error.message);
        return { js: null };
    }
}

// Write manifest file
function writeManifest(assets) {
    const manifest = {
        css: assets.css,
        js: assets.js,
        timestamp: new Date().toISOString()
    };

    fs.writeFileSync(
        path.join(distDir, 'manifest.json'),
        JSON.stringify(manifest, null, 2)
    );
    console.log('Manifest updated');
}

// Main build function
async function build() {
    console.log('Building...\n');
    cleanDist();

    const cssResult = compileSass();
    const jsResult = await bundleJs();

    writeManifest({
        css: cssResult.css,
        js: jsResult.js
    });

    console.log('\nBuild complete!');
}

// Watch mode
async function watch() {
    console.log('Starting watch mode...\n');

    // Initial build
    await build();

    // Watch SCSS files
    const scssDir = path.join(srcDir, 'styles/src');
    fs.watch(scssDir, { recursive: true }, async (eventType, filename) => {
        if (filename && filename.endsWith('.scss')) {
            console.log(`\nSCSS changed: ${filename}`);
            const cssResult = compileSass();
            const manifest = JSON.parse(fs.readFileSync(path.join(distDir, 'manifest.json')));
            manifest.css = cssResult.css;
            manifest.timestamp = new Date().toISOString();
            fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
        }
    });

    // Watch JS files
    const jsDir = path.join(srcDir, 'scripts/src');
    fs.watch(jsDir, { recursive: true }, async (eventType, filename) => {
        if (filename && filename.endsWith('.js')) {
            console.log(`\nJS changed: ${filename}`);
            const jsResult = await bundleJs();
            const manifest = JSON.parse(fs.readFileSync(path.join(distDir, 'manifest.json')));
            manifest.js = jsResult.js;
            manifest.timestamp = new Date().toISOString();
            fs.writeFileSync(path.join(distDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
        }
    });

    console.log('Watching for changes...');
}

// Run
if (isWatch) {
    watch();
} else {
    build();
}
