# Gallery overlay: DB state + late joiners + video sync

This document describes how to finish the feature using the `live_show_gallery_states` table (migration: `2026_03_19_000000_create_live_show_gallery_states_table.php`).

## 1. Run the migration

```bash
php artisan migrate
```

## 2. Schema reference

| Column | Purpose |
|--------|--------|
| `live_show_id` | One row per show (unique). |
| `is_visible` | Whether the overlay should be shown to viewers who load or refresh. |
| `gallery_media_id` | Optional FK to `gallery_media` (for traceability). |
| `url` | Denormalized media URL (same idea as `ShowGalleryImageEvent`). |
| `media_type` | `image` or `video`. |
| `playback_started_at` | **Video only:** server timestamp when playback was started (anchor for sync). **Null for images.** |
| `video_duration_seconds` | **Video only (optional):** file length in seconds; use to clamp seek (`min(elapsed, duration)`). **Null for images.** |

**Image row (visible):** `is_visible = true`, `media_type = image`, `url` set, `playback_started_at = null`, `video_duration_seconds = null`.

**Video row (visible):** `is_visible = true`, `media_type = video`, `url` set, `playback_started_at = now()` (or the instant the host’s player actually starts), optional `video_duration_seconds`.

**Hidden:** `is_visible = false`; you can null out `url` / `media_type` / time fields or keep last values for admin—pick one convention and stick to it.

## 3. Eloquent model

- Implemented: `App\Models\LiveShowGalleryState` with `$fillable` / `$casts`.
- `LiveShow::galleryState()` is a `hasOne(LiveShowGalleryState::class)` relation.

## 4. Admin: when showing / hiding gallery (server)

**Show (e.g. `LiveShowController::showGalleryImage`):**

1. Validate media is attached to the show (already done).
2. `updateOrCreate` on `live_show_id` with:
   - `is_visible` = true  
   - `gallery_media_id`, `url`, `media_type` from `GalleryMedia`  
   - If `media_type === 'video'`: set `playback_started_at` to `now()` (or `Carbon::now('UTC')`—be consistent).  
   - If `media_type === 'video'` and you know duration: set `video_duration_seconds`.  
   - If `media_type === 'image'`: set `playback_started_at` and `video_duration_seconds` to `null`.
3. Dispatch `ShowGalleryImageEvent` with payload including at least `url`, `type`, and for video **the same `playback_started_at` (or ISO string / ms)** so live clients don’t rely only on DB.

**Hide (e.g. `hideGalleryImage`):**

1. Update the row: `is_visible = false` (and optionally clear media fields).
2. Dispatch `HideGalleryImageEvent` as today.

Use a **DB transaction** around write + broadcast if you want both to stay aligned.

## 5. Public API or initial page data (late joiners)

**You must** expose current state so users who open the page **after** the Pusher event still see the overlay.

- Add something like `GET /live-show/{id}/gallery-overlay-state` (or include JSON in the Blade view) returning:
  - `is_visible`
  - `url`, `media_type`
  - `playback_started_at` (ISO 8601 or Unix ms)
  - `video_duration_seconds` if set
  - Optionally `server_time_ms` to reduce client clock skew when computing elapsed time

## 6. Front-end (`live-show.blade.php`)

**On load (after DOM ready):**

1. Fetch state (or read from embedded JSON).
2. If `!is_visible` → do nothing (or ensure overlay hidden).
3. If `is_visible` and `media_type === 'image'` → `showGalleryOverlay({ type: 'image', src: url })`.
4. If `is_visible` and `media_type === 'video'`:
   - Set `video.src`, then on `loadedmetadata` or `canplay`:
   - `elapsed = (serverNow - playbackStartedAt) / 1000` (use server-provided times if you expose them).
   - If `video_duration_seconds` is set: `elapsed = min(elapsed, video_duration_seconds)`.
   - `video.currentTime = elapsed` then `play()` as you do today.

**Pusher:** Keep subscribing to `ShowGalleryImageEvent` / `HideGalleryImageEvent`; extend the event payload so `ShowGalleryImageEvent` includes `playback_started_at` (and duration) for videos so connected clients stay in sync without refetching.

## 7. Pusher / `broadcastWith()`

Update `ShowGalleryImageEvent::broadcastWith()` to include:

- `url`, `type` (existing)
- `playback_started_at` (null for image)
- `video_duration_seconds` (optional)

Client handler: branch on `type`; for video, set `currentTime` from elapsed time instead of always `0`.

## 8. ZEGO / CDN

- Gallery files are **HTML5 overlay** + **your URL**; **not** ZEGO’s responsibility for “URL sync.”
- Serve **video URLs from a CDN** (or object storage) so many viewers don’t hammer the app server.

## 9. Admin HTTP APIs (`MediaGalleryController`)

All routes are under `admin/`, require authenticated admin session (`auth` middleware). Use `Accept: application/json`, CSRF token, and session cookie for AJAX from the dashboard.

| Method | Route name | Path | Purpose |
|--------|------------|------|---------|
| GET | `admin.live-shows.gallery-stream-state` | `admin/live-shows/{live_show}/gallery-stream-state` | Returns `showing` + `state` (or `null` if nothing visible). |
| POST | `admin.live-shows.gallery-stream.show` | `admin/live-shows/{live_show}/gallery-stream/show` | Body: `gallery_media_id`, optional `video_duration_seconds` (video). Persists row + broadcasts `ShowGalleryImageEvent`. |
| PATCH | `admin.live-shows.gallery-stream.update` | `admin/live-shows/{live_show}/gallery-stream` | Body (≥1): `gallery_media_id`, `video_duration_seconds`, `restart_playback`. Updates media/duration/restart; requires existing state row. |
| POST | `admin.live-shows.gallery-stream.visibility` | `admin/live-shows/{live_show}/gallery-stream/visibility` | Body: `is_visible` (`false` = hide + `HideGalleryImageEvent`; `true` = re-show last media if state exists). |

Legacy stream-management endpoints (`show-gallery-image` / `hide-gallery-image` on `LiveShowController`) also persist `live_show_gallery_states` and broadcast with timing fields.

## 10. Checklist

- [ ] Migration run  
- [ ] Model + `LiveShow` relation  
- [ ] `showGalleryImage` / `hideGalleryImage` persist state  
- [ ] Event payload extended for video timing  
- [ ] GET or embedded JSON for initial state  
- [ ] Blade: hydrate overlay + seek video on load  
- [ ] Test: open show → overlay; new tab joins late → same image/video position  

---

*Generated for the live.baaboo project; adjust routes and names to match your implementation.*
