document.addEventListener('DOMContentLoaded', function () {
    const messages = document.querySelectorAll('.message');

    messages.forEach((m) => {
        setTimeout(() => m.classList.add('invisible'), 5000);
    });
});
