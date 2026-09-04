import '../../vendor/masmerise/livewire-toaster/resources/js'; // 👈
import { initLanding } from './landing.js';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanding);
} else {
    initLanding();
}
