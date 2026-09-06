(() => {
    class CctvReview extends HTMLElement {
        connectedCallback() {
            if (this.initialised) return;
            this.initialised = true;

            const dataNode = this.querySelector('script[type="application/json"]');
            this.video = this.querySelector('video');
            this.videoShell = this.querySelector('[data-cctv-video-shell]');
            this.stage = this.querySelector('[data-cctv-stage]');
            this.overlay = this.querySelector('[data-cctv-overlay]');
            this.timeLabel = this.closest('.swm-cctv-player-panel')?.querySelector('[data-cctv-time]');
            this.status = this.querySelector('[data-cctv-video-status]');
            this.threshold = this.querySelector('[data-cctv-confidence]');
            this.thresholdValue = this.querySelector('[data-cctv-confidence-value]');
            this.reviewRoot = this.closest('[data-cctv-review-root]');
            this.eventButtons = [...this.reviewRoot?.querySelectorAll('[data-cctv-event]') ?? []];

            try {
                this.data = JSON.parse(dataNode?.textContent ?? '{}');
            } catch {
                this.data = { events: [] };
            }

            this.events = Array.isArray(this.data.events) ? this.data.events : [];
            this.video?.addEventListener('loadedmetadata', () => this.render());
            this.video?.addEventListener('timeupdate', () => this.render());
            this.video?.addEventListener('durationchange', () => this.render());
            this.video?.addEventListener('error', () => {
                if (!this.status) return;
                this.status.textContent = 'The video could not be played. Check that local storage is linked.';
                this.status.hidden = false;
            });
            this.threshold?.addEventListener('input', () => this.render());
            this.eventButtons.forEach((button) => button.addEventListener('click', () => this.jumpTo(button)));
            if (this.stage) {
                this.resizeObserver = new ResizeObserver(() => this.fitVideo());
                this.resizeObserver.observe(this.stage);
            }
            this.render();
        }

        disconnectedCallback() {
            this.resizeObserver?.disconnect();
        }

        fitVideo() {
            if (!this.stage || !this.videoShell) return;
            const ratio = (this.video?.videoWidth || 1080) / (this.video?.videoHeight || 766);
            const width = Math.min(this.stage.clientWidth, this.stage.clientHeight * ratio);
            this.videoShell.style.width = `${width}px`;
            this.videoShell.style.height = `${width / ratio}px`;
        }

        jumpTo(button) {
            const event = this.events[Number(button.dataset.cctvEvent)];
            if (!event || !this.video?.duration) return;

            this.video.currentTime = Number(event.timestamp_ratio) * this.video.duration;
            this.video.play().catch(() => {});
            this.render();
        }

        render() {
            const duration = this.video?.duration || 0;
            const currentTime = this.video?.currentTime || 0;
            const threshold = Number(this.threshold?.value ?? 50);
            this.fitVideo();
            const empty = this.reviewRoot?.querySelector('[data-cctv-filter-empty]');
            if (empty) empty.hidden = this.events.length === 0 || this.events.some((event) => Number(event.confidence) >= threshold);
            if (this.videoShell && this.video?.videoWidth && this.video?.videoHeight) {
                this.videoShell.style.aspectRatio = `${this.video.videoWidth} / ${this.video.videoHeight}`;
            }
            if (this.thresholdValue) this.thresholdValue.textContent = `${threshold}%`;
            if (this.timeLabel) this.timeLabel.textContent = `${this.formatTime(currentTime)} / ${duration ? this.formatTime(duration) : '--:--'}`;

            this.eventButtons.forEach((button, index) => {
                const event = this.events[index];
                const visible = Number(event?.confidence ?? 0) >= threshold;
                const timeNode = button.querySelector('[data-cctv-event-time]');
                if (timeNode && event) {
                    timeNode.textContent = duration
                        ? this.formatTime(Number(event.timestamp_ratio) * duration)
                        : `${Math.round(Number(event.timestamp_ratio) * 100)}% of clip`;
                }
                button.hidden = !visible;
                button.classList.toggle('is-active', visible && duration > 0 && Math.abs(currentTime - (Number(event.timestamp_ratio) * duration)) < Math.max(1.5, duration * 0.06));
            });

            if (!this.overlay) return;
            this.overlay.replaceChildren();
            if (!duration) return;

            this.events
                .filter((event) => Number(event.confidence) >= threshold)
                .filter((event) => Math.abs(currentTime - (Number(event.timestamp_ratio) * duration)) < Math.max(1.5, duration * 0.06))
                .forEach((event) => {
                    const box = document.createElement('div');
                    const bounds = event.box ?? {};
                    box.className = `swm-cctv-box${event.incident ? ' is-incident' : ''}`;
                    box.style.left = `${Number(bounds.x) || 0}%`;
                    box.style.top = `${Number(bounds.y) || 0}%`;
                    box.style.width = `${Number(bounds.width) || 10}%`;
                    box.style.height = `${Number(bounds.height) || 10}%`;

                    const label = document.createElement('span');
                    label.textContent = `${event.label} · ${Number(event.confidence).toFixed(1)}%`;
                    box.append(label);
                    this.overlay.append(box);
                });
        }

        formatTime(seconds) {
            const total = Math.max(0, Math.floor(Number(seconds) || 0));
            return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
        }
    }

    if (!customElements.get('swm-cctv-review')) {
        customElements.define('swm-cctv-review', CctvReview);
    }
})();
