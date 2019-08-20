<div class="container">
    <% if $Breadcrumbs %>
        <% include Breadcrumbs Breadcrumbs=$Breadcrumbs %>
    <% end_if %>
    <h1>$Title</h1>

    <% if $Link %>
        <p>
            This form allows for multiple sessions (singular or multiple users).
            You can access or contribute to this form using the following link:
            <a href="$Link">$Link</a>
        </p>
    <% end_if %>

    $Content.RichLinks
    $Form
</div>
