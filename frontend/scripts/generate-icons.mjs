// Erzeugt alle Icon-/Splash-Assets aus dem Nidula-Markenlogo (Nest mit Haus &
// Herz). Quelle: frontend/brand/nidula-mark.png (freigestellte Marke, transparent).
// Aufruf: `node scripts/generate-icons.mjs`, danach
// `npx capacitor-assets generate --android` (bzw. --ios) für die nativen Dichten.
import { mkdir, readFile } from 'node:fs/promises'
import sharp from 'sharp'

// Markenfarben (aus dem Logo abgeleitet)
const SAGE = '#8c9878' // Kachel-Grün hell
const SAGE_DK = '#6e7e64' // Kachel-Grün dunkel (Verlauf)
const DEEP = '#36432f' // Splash dunkel
const DEEP_DK = '#2a3526'

const MARK = await readFile('brand/nidula-mark.png')

const bg = (size, c1, c2) => `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="${c1}"/>
      <stop offset="1" stop-color="${c2}"/>
    </linearGradient>
  </defs>
  <rect width="${size}" height="${size}" fill="url(#g)"/>
</svg>`

const bgPng = (size, c1, c2) => sharp(Buffer.from(bg(size, c1, c2))).png().toBuffer()
const markPng = (px) =>
  sharp(MARK).resize(px, px, { fit: 'contain', background: { r: 0, g: 0, b: 0, alpha: 0 } }).png().toBuffer()

async function onBg(size, c1, c2, frac, out) {
  await sharp(await bgPng(size, c1, c2))
    .composite([{ input: await markPng(Math.round(size * frac)), gravity: 'center' }])
    .png()
    .toFile(out)
}

async function transparentMark(size, frac, out) {
  await sharp({
    create: { width: size, height: size, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } },
  })
    .composite([{ input: await markPng(Math.round(size * frac)), gravity: 'center' }])
    .png()
    .toFile(out)
}

await mkdir('assets', { recursive: true })

// App-Icon (voll, System rundet) + adaptives Icon (Vorder-/Hintergrund getrennt).
await onBg(1024, SAGE, SAGE_DK, 0.74, 'assets/icon-only.png')
await sharp(await bgPng(1024, SAGE, SAGE_DK)).png().toFile('assets/icon-background.png')
await transparentMark(1024, 0.78, 'assets/icon-foreground.png')

// Splash hell (Salbei) + dunkel.
await onBg(2732, SAGE, SAGE_DK, 0.24, 'assets/splash.png')
await onBg(2732, DEEP, DEEP_DK, 0.24, 'assets/splash-dark.png')

// Web-/PWA-Icon.
await mkdir('public', { recursive: true })
await onBg(512, SAGE, SAGE_DK, 0.74, 'public/icon.png')

console.log('Nidula-Assets erzeugt (aus brand/nidula-mark.png)')
