<div id="users_view">
    <table style="border: 1px solid black">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Link</th>
        </tr>
        </thead>
        <tbody>

        <?php
        if (isset($users)) {
            foreach ($users as $user)
                echo sprintf('
            <tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td><a href="http://localhost/users/%s">See</a></td>
            </tr>
    ', $user->getID(), $user->getName(), $user->getEmail(), $user->getID());
        } ?>

        </tbody>
    </table>

    <form style="margin-top: 10px">
        <label>
            Name:
            <input type="text" id="name"">
        </label>
        <label>
            Email:
            <input type="text" id="email"">
        </label>
    </form>

    <button onclick="sendRequest()">Send</button>

    <script>
        function sendRequest() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;


            const data = {id: 0, name: name, email: email};
            const encodedData = `user=${encodeURIComponent(JSON.stringify(data))}`;

            fetch('http://localhost/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Response-Layout': 'part',
                    'X-Response-type': 'text/html',
                },
                body: encodedData
            }).then(response => {
                let jsonMessage = response.headers.get('x-action-Messages');
                let messages = JSON.parse(jsonMessage)

                console.log(messages)
            })
        }

        function updatePartContent() {
            fetch('http://localhost/users', {
                headers: new Headers({
                    'Content-type': 'text/html',
                    'X-Response-Layout': 'part',
                    'X-Response-type': 'text/html',

                })
            })
                .then(response => response.text())
                .then(data => {
                    let userTable = document.getElementById('users_view')
                    userTable.innerHTML = data
                })
                .catch(error => {
                    console.error('Error:', error)
                })
        }
    </script>
</div>
