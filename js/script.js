document.addEventListener('DOMContentLoaded', function () {
    // Get deadline from global variable or default
    const dateString = window.DRAW_DATE ? window.DRAW_DATE : new Date().toDateString();
    const dest = new Date(dateString).getTime();

    let x = setInterval(function () {
        let now = new Date().getTime();
        let t = dest - now;

        let days = Math.floor(t / (1000 * 60 * 60 * 24));
        let hours = Math.floor((t % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((t % (1000 * 60)) / 1000);

        if (document.getElementById("days")) {
            document.getElementById("days").innerHTML = days < 10 ? '0' + days : days;
            document.getElementById("hours").innerHTML = hours < 10 ? '0' + hours : hours;
            document.getElementById("minutes").innerHTML = minutes < 10 ? '0' + minutes : minutes;
            document.getElementById("seconds").innerHTML = seconds < 10 ? '0' + seconds : seconds;
        }

        if (t < 0) {
            clearInterval(x);
            if (document.getElementById("days")) {
                document.querySelector(".countdown-container").innerHTML = "<h2 style='color:var(--primary-color)'>DRAW CLOSED</h2>";
            }
        }
    }, 1000);

    // Copy to clipboard
    const copyBtn = document.querySelector('.copy-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const input = document.querySelector('.referral-input input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                copyBtn.textContent = "Copied!";
                setTimeout(() => copyBtn.textContent = "Copy", 2000);
            });
        });
    }
});
