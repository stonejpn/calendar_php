window.addEventListener('load', function() {
    // window.loadの後で、要素を検索する
    document.querySelector('#change-start-date-sunday').addEventListener('change', startDateChanged);
    document.querySelector('#change-start-date-monday').addEventListener('change', startDateChanged);
})

function startDateChanged() {
    let start_date = '';

    console.log('start-date: changed');

    sunday = document.querySelector('#change-start-date-sunday');
    // console.log(`  sunday: ${sunday.checked}`);
    monday = document.querySelector('#change-start-date-monday');
    // console.log(`  monday: ${monday.checked}`);

    if (sunday.checked) {
        start_date = 'sunday';
    } else if (monday.checked) {
        start_date = 'monday';
    }

    // Cookieをセットして、リロードする
    console.log(`cookie: ${start_date}`);
    document.cookie = `start-date=${start_date}; path=/; SameSite=strict`;
    location.reload();
}