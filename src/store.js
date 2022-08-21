import { writable } from 'svelte/store';

export const access_token = writable('');

access_token.subscribe((token) => {
    if (typeof localStorage !== 'undefined' && token) {
        localStorage.setItem('access_token', token);
    }
});

if (typeof localStorage !== 'undefined') {
    const token = localStorage.getItem('access_token');
    if (token) {
        access_token.set(token);
    }
}
