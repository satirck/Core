<table style="border: 1px solid black">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>

    <?php
    if (isset($users)) {
        foreach ($users as $user)
            echo sprintf('
        <tbody>
            <tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>
        </tbody>
    ', $user->getID(), $user->getName(), $user->getEmail());
    } ?>

    </tbody>
</table>

<form style="margin-top: 10px">
    <label>
        Name:
        <input type="text" id="name">
    </label>
    <label>
        Email:
        <input type="text" id="email">
    </label>
</form>

<button onclick="sendRequest()">Send</button>

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


        const data = {id: 0, name: name, email: email};
        const encodedData = `user=${encodeURIComponent(JSON.stringify(data))}`;

        fetch('http://localhost/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: encodedData
        })
            .then(response => response.text())
            .then(
                alert('Your user have been saved')
            )
            .catch(error => {
                console.error('Error:', error)
            })

        fetch('http://localhost/users')
            .then(response => response.text())
            .then(data => {
                document.open()
                document.write(data)
                document.close()
            })
            .catch(error => {
                console.error('Error:', error)
            })
    }
</script>
