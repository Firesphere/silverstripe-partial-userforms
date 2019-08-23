<div class="container">
    <h1>$Title</h1>

    <% if $PartialLink %>
        <p>
            This form allows for multiple sessions (singular or multiple users).
            You can access or contribute to this form using the following link:
            <a href="$PartialLink">$PartialLink</a>
        </p>
    <% end_if %>

    $Content
    $Form
</div>
