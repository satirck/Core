<table style="border: 1px solid black" id="users_table">
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
        <input type="text" id="name" value="Mikita">
    </label>
    <label>
        Email:
        <input type="text" id="email" value="test@gmail.com">
    </label>
</form>

<button onclick="sendRequest()">Send</button>

<button onclick="updatePartContent()">Update Part</button>
<button onclick="updateFullContent()">Update Full</button>

<script>
    function validateEmail(email) {
        const regex = /(\w+)@(\w+)\.(\w+)/gm;

        return regex.test(email);
    }

    function sendRequest() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;

        if (name === '' || email === '') {
            alert('Name and email shouldn`t be empty')

            return 0;
        }

        let isCorrectMail = validateEmail(email);

        if (!isCorrectMail) {
            alert('Mail is not correct!')

            return 0;
        }

        // fetch('http://localhost/users', {
        //     method: 'POST',
        //     headers: new Headers({
        //         'Content-type: text/html',
        //         'X-Response-Content-Type': 'text/html',
        //             'X-Response-Layout'
        //     }),
        //     body: encodedData
        // })
        //     .then(response => response.text())
        //     .then(data => {
        //         console.log(data);
        //     })
        //     .catch(error => {
        //         console.error('Error:', error)
        //     })
    }

    function updatePartContent() {
        fetch('http://localhost/users', {
            headers: new Headers({
                'Content-type': 'text/html',
                'X-Response-type': 'text/html',
                'X-Response-Layout', 'yes'
            })
        })
            .then(response => response.text())
            .then(data => {
                console.log(data)
            })
            .catch(error => {
                console.error('Error:', error)
            })
    }

    function updateFullContent() {
        fetch('http://localhost/users', {
            headers: new Headers({
                'Content-type': 'text/html',
                'X-Response-type': 'text/html',
            })
        })
            .then(response => response.text())
            .then(data => {
                console.log(data)
            })
            .catch(error => {
                console.error('Error:', error)
            })
    }
</script>
