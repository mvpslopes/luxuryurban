(function () {
    'use strict';

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    function setVal(root, fill, label, value) {
        var v = Math.min(100, Math.max(0, Math.round(value)));
        if (fill) fill.style.width = v + '%';
        if (label) label.textContent = v + '%';
        root.setAttribute('aria-valuenow', String(v));
    }

    window.LuxuryProgress = {
        reset: function (root) {
            if (!root) return;
            var fill = root.querySelector('.luxury-progress__fill');
            var label = root.querySelector('.luxury-progress__label');
            setVal(root, fill, label, 0);
        },

        animateToFull: function (root, duration, cb) {
            if (!root) {
                if (cb) cb();
                return;
            }

            duration = typeof duration === 'number' ? duration : 1600;
            var fill = root.querySelector('.luxury-progress__fill');
            var label = root.querySelector('.luxury-progress__label');
            var start = Date.now();
            var raf = 0;

            setVal(root, fill, label, 0);

            function tick() {
                var t = Math.min((Date.now() - start) / duration, 1);
                setVal(root, fill, label, easeOutCubic(t) * 100);
                if (t < 1) {
                    raf = requestAnimationFrame(tick);
                } else {
                    setVal(root, fill, label, 100);
                    if (cb) setTimeout(cb, 450);
                }
            }

            raf = requestAnimationFrame(tick);
        },

        start: function (root, options) {
            if (!root) {
                return { complete: function (cb) { if (cb) cb(); }, stop: function () {} };
            }

            options = options || {};
            var fill = root.querySelector('.luxury-progress__fill');
            var label = root.querySelector('.luxury-progress__label');
            var maxHold = typeof options.maxPercent === 'number' ? options.maxPercent : 92;
            var duration = typeof options.duration === 'number' ? options.duration : 2400;
            var start = Date.now();
            var raf = 0;
            var creepIv = null;
            var stopped = false;

            function tick() {
                if (stopped) return;
                var elapsed = Date.now() - start;
                var t = Math.min(elapsed / duration, 1);
                setVal(root, fill, label, easeOutCubic(t) * maxHold);
                if (t < 1) {
                    raf = requestAnimationFrame(tick);
                } else {
                    creep();
                }
            }

            function creep() {
                creepIv = setInterval(function () {
                    if (stopped) return;
                    var current = parseInt(root.getAttribute('aria-valuenow') || '0', 10);
                    if (current >= maxHold) return;
                    setVal(root, fill, label, current + 1);
                }, 140);
            }

            function stopTimers() {
                stopped = true;
                if (raf) cancelAnimationFrame(raf);
                if (creepIv) clearInterval(creepIv);
            }

            raf = requestAnimationFrame(tick);

            return {
                complete: function (cb) {
                    stopTimers();
                    var from = parseInt(root.getAttribute('aria-valuenow') || '0', 10);
                    var t0 = Date.now();
                    var finishMs = 380;

                    function finish() {
                        var t = Math.min((Date.now() - t0) / finishMs, 1);
                        setVal(root, fill, label, from + (100 - from) * easeOutCubic(t));
                        if (t < 1) {
                            requestAnimationFrame(finish);
                        } else if (cb) {
                            cb();
                        }
                    }

                    requestAnimationFrame(finish);
                },
                stop: stopTimers
            };
        }
    };
}());
