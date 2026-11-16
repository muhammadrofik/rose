import Chart from 'chart.js/auto';
import * as LucideIcons from 'lucide';

window.Chart = Chart;
window.lucide = LucideIcons;

// Initialize Lucide icons on DOMContentLoaded and Livewire updates
function initIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

document.addEventListener('DOMContentLoaded', initIcons);
document.addEventListener('livewire:navigated', initIcons);
document.addEventListener('livewire:load', initIcons);
document.addEventListener('livewire:initialized', initIcons);

window.initLucide = initIcons;
