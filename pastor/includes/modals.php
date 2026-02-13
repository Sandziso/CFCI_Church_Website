<?php
// pastor/includes/modals.php
?>
<!-- Announcement Modal -->
<div class="modal" id="announcementModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Announcement</h3>
            <button class="close-modal" onclick="closeModal('announcementModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="announcementForm">
                <input type="hidden" name="action" value="add_announcement">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" class="form-control" rows="6" required></textarea>
                </div>
                <div class="form-group">
                    <label for="expires_at">Expires At (Optional)</label>
                    <input type="date" name="expires_at" id="expires_at" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> Publish Announcement
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Event</h3>
            <button class="close-modal" onclick="closeModal('eventModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="eventForm">
                <input type="hidden" name="action" value="add_event">
                <div class="form-group">
                    <label for="event_title">Event Title</label>
                    <input type="text" name="event_title" id="event_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" name="event_date" id="event_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" name="start_time" id="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" name="end_time" id="end_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-calendar-plus"></i> Add Event
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Sermon Modal -->
<div class="modal" id="sermonModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Sermon</h3>
            <button class="close-modal" onclick="closeModal('sermonModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" id="sermonForm">
                <input type="hidden" name="action" value="add_sermon">
                <div class="form-group">
                    <label for="sermon_title">Sermon Title</label>
                    <input type="text" name="sermon_title" id="sermon_title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="sermon_date">Sermon Date</label>
                    <input type="date" name="sermon_date" id="sermon_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="audio_url">Audio URL (Optional)</label>
                    <input type="url" name="audio_url" id="audio_url" class="form-control" placeholder="https://example.com/audio.mp3">
                </div>
                <div class="form-group">
                    <label for="video_url">Video URL (Optional)</label>
                    <input type="url" name="video_url" id="video_url" class="form-control" placeholder="https://youtube.com/watch?v=...">
                </div>
                <div class="form-group">
                    <label for="notes_text">Notes/Transcript (Optional)</label>
                    <textarea name="notes_text" id="notes_text" class="form-control" rows="6"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-video"></i> Add Sermon
                </button>
            </form>
        </div>
    </div>
</div>