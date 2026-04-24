<x-layouts.docs title="Booking Flow">
    <div class="container">
        <h1>Application Flows</h1>
        <div class="env-badge">{{ app()->environment() }}</div>
    </div>

    <div class="container-large">
        <div class="panel">
            <h2>1. Create a booking</h2>
            <p>
                Caller sends <code>POST /api/v1/bookings</code> with the room ID, an opaque
                <code>user_uid</code>, a title, and a start/end timestamp. The request passes
                through the layers:
            </p>
            <ul>
                <li><code>ValidateApiKey</code> middleware verifies <code>X-API-Key</code>.</li>
                <li><code>StoreBookingRequest</code> validates fields (including <code>ends_at &gt; starts_at</code> and that the room exists).</li>
                <li><code>BookingController::store</code> builds a <code>StoreBookingDTO</code> and delegates to <code>CreateBookingAction</code>.</li>
                <li><code>CreateBookingAction</code> opens a DB transaction, calls <code>BookingRepository::hasConflict()</code>, persists the booking, and dispatches <code>BookingCreatedMail</code>.</li>
                <li>Response is serialized via <code>BookingResource</code> and returned with <code>201 Created</code>.</li>
            </ul>
        </div>

        <div class="panel">
            <h2>2. Overlap detection</h2>
            <p>
                Two bookings on the same room overlap when:
            </p>
            <pre>existing.starts_at &lt; new.ends_at  AND  existing.ends_at &gt; new.starts_at</pre>
            <p>
                Implemented in <code>BookingRepository::hasConflict()</code>. Adjacent slots
                (<code>10:00–11:00</code> then <code>11:00–12:00</code>) are allowed. On conflict,
                <code>CreateBookingAction</code> throws <code>BookingConflictException</code>
                and the controller returns <code>409 Conflict</code>.
            </p>
        </div>

        <div class="panel">
            <h2>3. List bookings</h2>
            <p>
                <code>GET /api/v1/bookings</code> requires exactly one filter —
                <code>user_uid</code> or <code>room_id</code>. <code>ListBookingsAction</code>
                branches to the appropriate repository method; results are sorted by
                <code>starts_at</code> and returned as a <code>BookingResource</code> collection
                with the room eager-loaded.
            </p>
        </div>

        <div class="panel">
            <h2>4. Booking confirmation email</h2>
            <p>
                <code>CreateBookingAction</code> dispatches <code>BookingCreatedMail</code>
                after persistence. In local/dev the mail is captured by
                <a href="http://localhost:1080" style="color:#3b82f6;text-decoration:none">Mailcatcher</a>.
                The recipient is derived from <code>user_uid</code> — callers in production
                should map <code>user_uid</code> to a real address upstream.
            </p>
        </div>

        <a class="back-link" href="/">&larr; Back to home</a>
    </div>
</x-layouts.docs>
