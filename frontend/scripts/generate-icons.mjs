// Erzeugt die Quell-Assets für @capacitor/assets aus dem Nidula-Logo (Nest mit
// Herz). Aufruf: `node scripts/generate-icons.mjs`.
// Danach `npx capacitor-assets generate --android` (bzw. --ios) -> native Icons/Splashes.
import { mkdir } from 'node:fs/promises'
import sharp from 'sharp'

// Nidula-Palette (warm, erdig)
const FOREST = '#3A5A40'
const FOREST_DK = '#2F4A35'
const FOREST_DEEP = '#1E3326'
const CREAM = '#F3E7D4'
const TERRA = '#E08A5F'

// Mark = Nest (cremefarbener Bogen) das ein Herz (Terrakotta) hält.
const mark = (nestColor, heartColor) => `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
  <!-- Herz, sitzt im Nest -->
  <path fill="${heartColor}" d="
    M512 660
    C 438 590, 322 540, 322 442
    C 322 376, 374 332, 432 332
    C 474 332, 500 360, 512 392
    C 524 360, 550 332, 592 332
    C 650 332, 702 376, 702 442
    C 702 540, 586 590, 512 660 Z" />
  <!-- Nest: Schale aus zwei Bögen + Zweig-Enden -->
  <g fill="none" stroke="${nestColor}" stroke-linecap="round" stroke-linejoin="round" stroke-width="66">
    <path d="M 196 612 C 300 812, 724 812, 828 612" />
    <path d="M 286 632 C 360 760, 664 760, 738 632" />
  </g>
  <g fill="none" stroke="${nestColor}" stroke-linecap="round" stroke-width="40">
    <path d="M 214 624 l -44 -10" />
    <path d="M 810 624 l 44 -10" />
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

const markPng = (px, nest, heart) =>
  sharp(Buffer.from(mark(nest, heart))).resize(px, px).png().toBuffer()
const bgPng = (size, c1, c2) => sharp(Buffer.from(bg(size, c1, c2))).png().toBuffer()

async function onBackground(size, c1, c2, frac, out, nest = CREAM, heart = TERRA) {
  const m = await markPng(Math.round(size * frac), nest, heart)
  await sharp(await bgPng(size, c1, c2))
    .composite([{ input: m, gravity: 'center' }])
    .png()
    .toFile(out)
}

async function transparentMark(size, frac, out, nest, heart) {
  await sharp({
    create: { width: size, height: size, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } },
  })
    .composite([{ input: await markPng(Math.round(size * frac), nest, heart), gravity: 'center' }])
    .png()
    .toFile(out)
}

await mkdir('assets', { recursive: true })

// App-Icon (Nest cremefarben + Terrakotta-Herz auf grünem Grund).
await onBackground(1024, FOREST, FOREST_DK, 0.66, 'assets/icon-only.png')
await sharp(await bgPng(1024, FOREST, FOREST_DK)).png().toFile('assets/icon-background.png')
await transparentMark(1024, 0.5, 'assets/icon-foreground.png', CREAM, TERRA)

// Splash hell (Nest grün + Herz Terrakotta auf Creme) und dunkel.
await onBackground(2732, '#F3ECE1', '#ECE2D2', 0.24, 'assets/splash.png', FOREST, TERRA)
await onBackground(2732, FOREST_DEEP, '#172619', 0.24, 'assets/splash-dark.png', CREAM, TERRA)

console.log('Nidula-Assets erzeugt in frontend/assets/')
