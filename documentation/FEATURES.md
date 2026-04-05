# Feature Backlog

Geplante und gewünschte Features für zukünftige Versionen.

---

## Geplante Veröffentlichung

**Status:** Offen
**Priorität:** Hoch

Ermöglicht es, einen Post für einen zukünftigen Zeitpunkt einzuplanen. Aktuell wird `published_at` automatisch auf `now()` gesetzt sobald der Status auf "Published" gewechselt wird — ein manuelles Datum ist im Formular nicht setzbar.

**Was geändert werden müsste:**

- `published_at` DateTimePicker im `PostForm` einblenden wenn Status = Published
- Neuen Status `Scheduled` im `PostStatus`-Enum ergänzen
- Einen Scheduler-Job (z.B. `schedule:run` stündlich) der geplante Posts automatisch veröffentlicht
- Admin-Tabelle: geplante Posts erkennbar machen (Badge "Scheduled")

---

## Like / Dislike für Posts

**Status:** Offen
**Priorität:** Mittel

Besucher sollen Posts mit einem Like oder Dislike bewerten können. Ohne Account, anonym per Session oder IP.

**Was geändert werden müsste:**

- Neue Tabelle `post_reactions` (post_id, type: like|dislike, ip_address, session_id)
- Livewire-Komponente für die Like/Dislike-Buttons inkl. optimistisches UI-Update
- Rate-Limiting pro IP um Manipulation zu verhindern (1 Vote pro Post pro IP)
- Anzeige der Zähler auf Post-Karte und im Post selbst
- Admin: Reaktionen pro Post in der Posts-Tabelle sichtbar machen

---

## Auto-Save im Admin

**Status:** Erledigt (`->unsavedChangesAlerts()` in `AdminPanelProvider`)
**Priorität:** Mittel

Ungespeicherte Änderungen an Posts und Pages gehen verloren wenn der Tab geschlossen oder navigiert wird. Filament v5 hat kein eingebautes Auto-Save.

**Was geändert werden müsste:**

- Entweder: periodisches Auto-Save per Livewire-Interval (z.B. alle 60 Sekunden) der den aktuellen Formularstand als Draft speichert
- Oder: Browser `beforeunload`-Event der bei ungespeicherten Änderungen einen Hinweis anzeigt (einfachere Variante)
- Filament v5 bietet `->unsavedChangesAlerts()` auf Panel-Ebene — das wäre die einfachste Lösung (zeigt Browser-Dialog bei Navigation weg vom Formular)
