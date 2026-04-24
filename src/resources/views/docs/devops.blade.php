<x-layouts.docs title="DevOps">
    <div class="container">
        <h1>DevOps</h1>
        <div class="env-badge">{{ app()->environment() }}</div>
    </div>

    <div class="container-large">
        <div class="panel">
            <h2>Docker stack</h2>
            <ul>
                <li><code>booking_microservice_php</code> — PHP 8.4 FPM (Laravel).</li>
                <li><code>booking_microservice_nginx</code> — HTTP :80.</li>
                <li><code>booking_microservice_postgres</code> — Postgres 18.1.</li>
                <li><code>booking_microservice_redis</code> — Redis 7 (cache).</li>
                <li><code>booking_microservice_mail</code> — Mailcatcher (SMTP :1025 / UI :1080).</li>
            </ul>
            <p>
                The PHP image installs only extensions we actually use:
                <code>pdo_pgsql</code>, <code>zip</code>, <code>intl</code>, <code>pcntl</code>,
                <code>sockets</code>, <code>redis</code>. The entrypoint handles
                <code>.env</code> copy, <code>composer install</code>, <code>APP_KEY</code>
                generation, a Redis ping check, migrations, and seeders on container start.
            </p>
        </div>

        <div class="panel">
            <h2>Pre-push hook</h2>
            <p>
                Runs inside the <code>php</code> container in this order:
            </p>
            <ul>
                <li><strong>PHP Lint</strong> — <code>php -l</code> across every non-vendor file.</li>
                <li><strong>Laravel Pint</strong> — style check with interactive auto-fix + commit offer on failure.</li>
                <li><strong>PHPStan (Larastan)</strong> — static analysis.</li>
            </ul>
            <p>
                Location: <code>.githooks/pre-push</code>. Git uses it via
                <code>core.hooksPath</code>, which is per-clone and not tracked.
                After cloning, run the one-time setup script from the host:
            </p>
            <pre>./bin/setup.sh</pre>
            <p>
                The hook talks to the running <code>php</code> container, so the stack must
                be up (<code>docker compose up -d</code>) when you push.
            </p>
        </div>

        <div class="panel">
            <h2>CI/CD</h2>
            <p>
                <code>.github/workflows/ci-cd.yml</code> on <code>main</code>:
            </p>
            <ul>
                <li>Boots a <code>postgres:18.1</code> service matching the local Docker image.</li>
                <li>Sets up PHP 8.4 with the same extension set as the Dockerfile.</li>
                <li>Runs PHP lint → Pint → PHPStan → migrations + seeds → <code>php artisan test</code> → <code>composer audit</code>.</li>
                <li>Deploy job is present but the Forge hooks are commented until they exist.</li>
            </ul>
        </div>

        <div class="panel">
            <h2>Testing</h2>
            <p>
                PHPUnit uses SQLite in-memory (<code>phpunit.xml</code>). Suites:
            </p>
            <ul>
                <li><code>tests/Feature/Api/BookingApiKeyTest.php</code> — API key middleware.</li>
                <li><code>tests/Feature/Api/CreateBookingTest.php</code> — create + overlap rules.</li>
                <li><code>tests/Feature/Api/ListBookingsTest.php</code> — list by user/room.</li>
                <li><code>tests/Unit/Actions/CreateBookingActionTest.php</code> — action-level conflict logic.</li>
            </ul>
        </div>

        <a class="back-link" href="/">&larr; Back to home</a>
    </div>
</x-layouts.docs>
