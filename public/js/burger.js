document.addEventListener('DOMContentLoaded', function () {
    const burger = document.querySelector('header .burger');
    const mobile = document.querySelector('header .mobile');
    const h1 = document.querySelector('.accueil h1');
    const croix = document.querySelector('header .croix');

    burger.addEventListener('click', function () {
        mobile.classList.toggle('ouvert');
        h1.classList.toggle('burger');
    });

    croix.addEventListener('click', function () {
        mobile.classList.toggle('ouvert');
        h1.classList.toggle('burger');
    });
});
