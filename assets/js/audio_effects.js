/**
 * Hunter Audio Engine - Web Audio API Synthesizer
 * Zero-dependency sound effects for terminal interaction
 */

window.HunterAudio = {
    ctx: null,
    muted: false,

    getAudioContext() {
        if (!this.ctx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                this.ctx = new AudioCtx();
            }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
        return this.ctx;
    },

    playBeep(freq = 520, duration = 0.1, type = 'sine') {
        if (this.muted) return;
        try {
            const ctx = this.getAudioContext();
            if (!ctx) return;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(freq, ctx.currentTime);

            gain.gain.setValueAtTime(0.04, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start();
            osc.stop(ctx.currentTime + duration);
        } catch (e) {
            // Audio context blocked by autoplay policy until interaction
        }
    },

    playSuccess() {
        if (this.muted) return;
        setTimeout(() => this.playBeep(523.25, 0.1, 'sine'), 0);
        setTimeout(() => this.playBeep(659.25, 0.1, 'sine'), 100);
        setTimeout(() => this.playBeep(783.99, 0.2, 'triangle'), 200);
    },

    playError() {
        if (this.muted) return;
        setTimeout(() => this.playBeep(240, 0.15, 'sawtooth'), 0);
        setTimeout(() => this.playBeep(180, 0.25, 'sawtooth'), 120);
    }
};
