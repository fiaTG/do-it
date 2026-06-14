// Erzeugt die Quell-Assets für @capacitor/assets aus einem SVG-Anker-Logo
// (maritime „Heimathafen"-Marke). Aufruf: `node scripts/generate-icons.mjs`.
// Danach `npx capacitor-assets generate --android` -> native Icons/Splashes.
import { mkdir } from 'node:fs/promises'
import sharp from 'sharp'

const NAVY = '#1F3347'
const OCEAN = '#274C63'
const DARK = '#13212c'
const LIGHT = '#EAF6FB'

const anchor = (stroke) => `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
  <g fill="none" stroke="${stroke}" stroke-width="46" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="512" cy="230" r="56"/>
    <path d="M512 286 V790"/>
    <path d="M408 352 H616"/>
    <path d="M236 546 C236 738 362 802 512 802 C662 802 788 738 788 546"/>
    <path d="M236 546 l-54 -26 M236 546 l-2 -60"/>
    <path d="M788 546 l54 -26 M788 546 l2 -60"/>
  </g>
</svg>`

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

const anchorPng = (px) =>
  sharp(Buffer.from(anchor(LIGHT))).resize(px, px).png().toBuffer()
const bgPng = (size, c1, c2) => sharp(Buffer.from(bg(size, c1, c2))).png().toBuffer()

async function onBackground(size, c1, c2, frac, out) {
  const a = await anchorPng(Math.round(size * frac))
  await sharp(await bgPng(size, c1, c2))
    .composite([{ input: a, gravity: 'center' }])
    .png()
    .toFile(out)
}

async function transparentAnchor(size, frac, out) {
  await sharp({
    create: { width: size, height: size, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } },
  })
    .composite([{ input: await anchorPng(Math.round(size * frac)), gravity: 'center' }])
    .png()
    .toFile(out)
}

await mkdir('assets', { recursive: true })

// App-Icon (voll) + adaptive Icon (Vorder-/Hintergrund getrennt; Anker kleiner
// wegen der Crop-Safezone bei Android-Adaptive-Icons).
await onBackground(1024, NAVY, OCEAN, 0.6, 'assets/icon-only.png')
await sharp(await bgPng(1024, NAVY, OCEAN)).png().toFile('assets/icon-background.png')
await transparentAnchor(1024, 0.46, 'assets/icon-foreground.png')

// Splash-Screens (hell + dunkel), Anker zentriert.
await onBackground(2732, NAVY, OCEAN, 0.22, 'assets/splash.png')
await onBackground(2732, DARK, NAVY, 0.22, 'assets/splash-dark.png')

console.log('Assets erzeugt in frontend/assets/')
