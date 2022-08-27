import { writable } from 'svelte/store';

export const login = writable(null);
export const edit_mode = writable(false);

login.subscribe((data) => {
    if (typeof localStorage !== 'undefined' && data) {
        localStorage.setItem('login', JSON.stringify(data));
    }
});

if (typeof localStorage !== 'undefined') {
    const data = JSON.parse(localStorage.getItem('login') || 'null');
    if (data) {
        login.set(data);
    }
}
