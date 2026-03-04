import { Ziggy as LocalZiggy } from './ziggy';
import * as routeImport from 'ziggy-js';

// Prefer the runtime Ziggy injected via @routes (uses correct APP_URL),
// fall back to the locally built config if not available (e.g., during SSR/tests).
const runtimeZiggy = (typeof window !== 'undefined' && window.Ziggy) ? window.Ziggy : LocalZiggy;

export const route = (name, params = {}, absolute = true, config = runtimeZiggy) => {
    return routeImport.route(name, params, absolute, config);
};
