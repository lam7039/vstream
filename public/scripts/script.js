document.addEventListener('DOMContentLoaded', () => {
    let video = document.getElementsByTagName('video')[0];
    let delta = 0.1;
    video.addEventListener('wheel', (e) => {
        if (e.deltaY > 0 && video.volume - delta >= 0) {
            video.volume -= delta;
        }
        if (e.deltaY < 0 && video.volume + delta <= 1) {
            video.volume += delta;
        }
    });
    video.addEventListener('keypress', (e) => {
        e.preventDefault();
        if (e.key === 'f') {
            video.requestFullscreen();
        }
        if (e.key === 'm') {
            video.muted = !video.muted;
        }
    });
});

//TODO: use ajax calls instead of redirects