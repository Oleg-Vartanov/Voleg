import { onMounted, onUnmounted } from 'vue'

// The ink design's paper texture (--ov-paper-texture, see ov-ink.scss) is an SVG filter that browsers
// re-run on every repaint. Render it once per theme into a bitmap and give CSS that instead
// (--ov-paper-bitmap); CSS falls back to the SVG until it is ready, or if rendering fails.

const sourceProperty = '--ov-paper-texture'
const bitmapProperty = '--ov-paper-bitmap'
const maxScale = 3

const bitmaps = new Map<string, Promise<string | null>>()

async function renderBitmap(texture: string): Promise<string | null> {
  const dataUrl = texture.match(/^url\("data:image\/svg\+xml,(.*)"\)$/)
  if (!dataUrl) return null

  // Re-render at device resolution; drawing the SVG scaled up would blur the grain.
  const scale = Math.min(window.devicePixelRatio || 1, maxScale)
  const svg = new DOMParser().parseFromString(decodeURIComponent(dataUrl[1]), 'image/svg+xml')
  const root = svg.documentElement
  const width = Number(root.getAttribute('width')) * scale
  const height = Number(root.getAttribute('height')) * scale
  root.setAttribute('width', String(width))
  root.setAttribute('height', String(height))

  const image = new Image()
  image.src =
    'data:image/svg+xml,' + encodeURIComponent(new XMLSerializer().serializeToString(root))
  await image.decode()

  const canvas = document.createElement('canvas')
  canvas.width = width
  canvas.height = height
  canvas.getContext('2d')?.drawImage(image, 0, 0)
  const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve))

  return blob ? URL.createObjectURL(blob) : null
}

function update(): void {
  const body = document.body
  const texture = getComputedStyle(body).getPropertyValue(sourceProperty).trim()

  if (!texture) {
    body.style.removeProperty(bitmapProperty)
    return
  }

  let bitmap = bitmaps.get(texture)
  if (!bitmap) {
    bitmap = renderBitmap(texture).catch(() => null)
    bitmaps.set(texture, bitmap)
  }

  bitmap.then((url) => {
    // The theme or design may have changed while rendering.
    if (getComputedStyle(body).getPropertyValue(sourceProperty).trim() !== texture) return

    if (url) body.style.setProperty(bitmapProperty, `url(${url})`)
    else body.style.removeProperty(bitmapProperty)
  })
}

/** Keeps the ink paper texture bitmap in sync with the design and color theme. */
export function useInkPaper() {
  const observer = new MutationObserver(update)

  onMounted(() => {
    update()
    observer.observe(document.body, {
      attributes: true,
      attributeFilter: ['data-ov-design', 'data-bs-theme']
    })
  })

  onUnmounted(() => observer.disconnect())
}
