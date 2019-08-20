<div class="container">
    <% include Breadcrumbs Breadcrumbs=$Breadcrumbs %>
    <div class="row">
        <h1>$Title</h1>

        <p>
            This form allows for multiple sessions (singular or multiple users). You can access or contribute to this form using the following link:
            <a href="$Link">$Link</a>
        </p>

        $Content.RichLinks
        $Form
    </div>
</div>
