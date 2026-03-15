// document.addEventListener('DOMContentLoaded', () => {
//     let video = document.getElementsByTagName('video')[0];
//     let delta = 0.1;
//     video.addEventListener('wheel', (e) => {
//         if (e.deltaY > 0 && video.volume - delta >= 0) {
//             video.volume -= delta;
//         }
//         if (e.deltaY < 0 && video.volume + delta <= 1) {
//             video.volume += delta;
//         }
//     });
//     video.addEventListener('keypress', (e) => {
//         e.preventDefault();
//         if (e.key === 'f') {
//             video.requestFullscreen();
//         }
//         if (e.key === 'm') {
//             video.muted = !video.muted;
//         }
//     });
// });

// import Router from './router.js';
import { ajax_get, ajax_post } from './ajax.js';

document.getElementsByClassName('playpause')[0].addEventListener('click', () => {
    ajax_get('/index.php');
    // ajax_get('http://www.example.org/example.txt');
});
