

import Alpine from 'alpinejs';
import financeJournal from './finance-journal';
import { initModernControls } from './modern-controls';

window.Alpine = Alpine;
window.financeJournal = financeJournal;

Alpine.start();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initModernControls);
} else {
    initModernControls();
}
