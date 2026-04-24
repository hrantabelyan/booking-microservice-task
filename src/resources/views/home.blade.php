@php
    /** @var string $env */
    /** @var string $appName */
    /** @var string $apiKey */
@endphp
<x-layouts.docs :title="$appName . ' — Dev Tools'">
    <div class="container">
        <h1>{{ $appName }}</h1>
        <div class="env-badge">{{ $env }}</div>
    </div>

    <div class="container-large">
        <div class="panel">
            <p>
                A JSON API microservice for booking meeting rooms. Callers create bookings
                for a room between <code>starts_at</code> and <code>ends_at</code>, and the
                service prevents overlaps for the same room. Bookings can be listed per user
                (<code>user_uid</code>) or per room (<code>room_id</code>).
            </p>
            <p>
                Authentication is a static API key in the <code>X-API-Key</code> header.
                User identity is opaque — <code>user_uid</code> is supplied by the caller
                and not validated against a users table.
            </p>
        </div>
    </div>

    <div class="container">
        <div class="links">
            <a href="/docs/flow">
                Application Flows
                <span>End-to-end booking workflow and conflict detection</span>
            </a>
            <a href="/docs/api">
                API Documentation
                <span>Interactive endpoint reference powered by Scramble</span>
            </a>
            <a href="/docs/devops">
                DevOps
                <span>CI/CD pipeline, code quality tools, and git hooks</span>
            </a>
        </div>

        <div class="download">
            <a href="/postman/collection">Download Postman Collection</a>
        </div>
    </div>

    <div class="container-large">
        <div class="panel">
            <h2>Local defaults</h2>
            <table class="kv">
                <tr><td>Base URL</td><td>http://localhost</td></tr>
                <tr><td>API prefix</td><td>/api/v1</td></tr>
                <tr><td>API key header</td><td>X-API-Key</td></tr>
                <tr><td>API key (dev)</td><td>{{ $apiKey }}</td></tr>
                <tr><td>Mailcatcher</td><td><a href="http://localhost:1080" style="color:#3b82f6;text-decoration:none">http://localhost:1080</a></td></tr>
            </table>
        </div>
    </div>
</x-layouts.docs>
