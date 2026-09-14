export const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

export const jsonHeaders = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken,
};

