import './echo';
import { initTheme } from './modules/theme';
import { startLiveClock } from './modules/clock';
import { initCustomerService } from './modules/cs';
import { initOperator } from './modules/operator';
import { initDisplay } from './modules/display';

// Inisialisasi modul global (layout header & tema)
initTheme();
startLiveClock();

// Inisialisasi modul per halaman (hanya berjalan jika elemen halaman aktif di DOM)
initCustomerService();
initOperator();
initDisplay();
