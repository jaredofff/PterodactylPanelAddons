declare module 'react' {
    export interface ChangeEvent<T = Element> {
        target: T & { value: string };
    }
    export function useState<T>(initialState: T | (() => T)): [T, (newState: T) => void];
    export function useEffect(effect: () => void | (() => void), deps?: any[]): void;
    export function useMemo<T>(factory: () => T, deps: any[] | undefined): T;
    export function useCallback<T extends (...args: any[]) => any>(callback: T, deps: any[]): T;
    export default any;
}

declare module 'react/jsx-runtime' {
    export const jsx: any;
    export const jsxs: any;
}

declare namespace JSX {
    interface IntrinsicElements {
        [elemName: string]: any;
    }
}

declare module 'react-dom';
declare module 'react-router-dom';
declare module 'axios';
declare module 'react-i18next';
declare module 'vue';
declare module 'vue-router';
declare module 'vue-i18n';

// Pterodactyl specific paths
declare module '@/state/server';
declare module '@/plugins/useFlash';
declare module '@/components/elements/Button';
declare module '@/components/elements/Spinner';
declare module '@/api/http';
