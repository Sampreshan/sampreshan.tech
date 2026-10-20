(function () {
    document.addEventListener('click', function (e) {
        var b = e.target.closest && e.target.closest('.sp-pigeon');
        if (!b) return;
        b.classList.remove('is-sending');
        void b.offsetWidth;
        b.classList.add('is-sending');
        setTimeout(function () { b.classList.remove('is-sending'); }, 900);
    });
})();
