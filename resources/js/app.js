import './bootstrap';
import './utils';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Utility: cn (class merge helper) is now imported from utils.js and attached to window
import { cn } from './utils';
window.cn = cn;

Alpine.start();
