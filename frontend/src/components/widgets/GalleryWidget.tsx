import { useEffect, useState } from 'react'
import { imagesApi } from '../../api'
import { APP_ICONS } from '../../lib/icons'
import type { ImageItem } from '../../types'
import WidgetCard from './WidgetCard'

export default function GalleryWidget({ onRemove }: { onRemove?: () => void }) {
  const [images, setImages] = useState<ImageItem[]>([])

  useEffect(() => {
    imagesApi
      .list()
      .then(({ images }) => setImages(images))
      .catch(() => {})
  }, [])

  const preview = images.slice(0, 6)

  return (
    <WidgetCard title="Galerie" icon={APP_ICONS.gallery} to="/gallery" onRemove={onRemove}>
      {preview.length === 0 ? (
        <p className="text-sm text-muted">Noch keine Bilder.</p>
      ) : (
        <div className="grid grid-cols-3 gap-1.5">
          {preview.map((img) => (
            <img
              key={img.id}
              src={img.thumbnail_url}
              alt={img.title ?? ''}
              loading="lazy"
              className="aspect-square w-full rounded-lg object-cover"
            />
          ))}
        </div>
      )}
    </WidgetCard>
  )
}
