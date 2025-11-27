document.addEventListener('DOMContentLoaded', function () {
    const burger = document.querySelector('header .burger');

    const mobile = document.querySelector('header .mobile');

    const h1 = document.querySelector('.accueil h1');

    const body = document.querySelector('body');

    burger.addEventListener('click', function () {
        mobile.classList.toggle('ouvert');

        h1.classList.toggle('burger');

        body.classList.toggle('scroll');
    });

    const croix = document.querySelector('header .croix');

    croix.addEventListener('click', function () {
        mobile.classList.toggle('ouvert');
        
        h1.classList.toggle('burger');

        body.classList.toggle('scroll');
    });

    const ancre = document.querySelectorAll('.mobile a');

    ancre.forEach(function (lien) {
        lien.addEventListener('click', function () {
            mobile.classList.toggle('ouvert');

            h1.classList.toggle('burger');

            body.classList.toggle('scroll');
        });
    });
});

