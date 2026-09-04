// Deploy script: registers/starts the Reverb server as a PM2 process.
// Usage: node deploy.js
//
// It checks whether PM2 is installed, installs it globally if missing,
// then starts (or restarts) the "reverb-main" process running
// `php artisan reverb:start`, and persists it with `pm2 save`.

import { execSync, spawnSync } from 'node:child_process';

const APP_NAME = 'reverb-main';
const HOST = '0.0.0.0';
const PORT = '8080';

function commandExists(cmd) {
  const checker = process.platform === 'win32' ? 'where' : 'which';
  return spawnSync(checker, [cmd], { stdio: 'ignore', shell: true }).status === 0;
}

if (!commandExists('pm2')) {
  console.log('[deploy] pm2 not found, installing globally via npm...');
  execSync('npm install -g pm2', { stdio: 'inherit' });
} else {
  console.log('[deploy] pm2 already installed.');
}

const list = JSON.parse(execSync('pm2 jlist').toString());
const exists = list.some((p) => p.name === APP_NAME);

if (exists) {
  console.log(`[deploy] "${APP_NAME}" already registered, restarting...`);
  execSync(`pm2 restart ${APP_NAME}`, { stdio: 'inherit' });
} else {
  console.log(`[deploy] Starting "${APP_NAME}"...`);
  // php.bat (Herd's shim) can't be spawned directly by PM2 on Windows
  // (spawn EINVAL), so it runs through cmd.exe instead.
  execSync(
    `pm2 start cmd --name ${APP_NAME} --interpreter none --cwd "${import.meta.dirname}" -- /c php artisan reverb:start --host=${HOST} --port=${PORT}`,
    { stdio: 'inherit', cwd: import.meta.dirname },
  );
}

execSync('pm2 save', { stdio: 'inherit' });
console.log('[deploy] Done.');
