const fs = require('fs');
const path = require('path');
const { ZipArchive } = require('archiver');

const root = path.resolve(__dirname, '..');
const pkg = JSON.parse(fs.readFileSync(path.join(root, 'package.json'), 'utf8'));
const deployDir = path.join(root, 'deploy');
const packageDir = path.join(deployDir, pkg.name);
const zipName = `${pkg.name}-${pkg.version}.zip`;
const zipPath = path.join(deployDir, zipName);
const tempZipPath = path.join(root, `.${zipName}.tmp`);

if (!fs.existsSync(packageDir)) {
	throw new Error('Deployment package directory does not exist. Run the copy step first.');
}

if (fs.existsSync(zipPath)) {
	fs.rmSync(zipPath);
}
if (fs.existsSync(tempZipPath)) {
	fs.rmSync(tempZipPath);
}

const output = fs.createWriteStream(tempZipPath);
const archive = new ZipArchive({ zlib: { level: 9 } });

output.on('close', () => {
	fs.renameSync(tempZipPath, zipPath);
	const sizeKb = (archive.pointer() / 1024).toFixed(1);
	console.log(`ZIP created: deploy/${zipName} (${sizeKb} KB)`);
});

archive.on('warning', (error) => {
	if (error.code === 'ENOENT') {
		console.warn('Archive warning:', error);
		return;
	}
	throw error;
});

archive.on('error', (error) => {
	throw error;
});

archive.pipe(output);
archive.directory(packageDir, pkg.name);
archive.finalize();
