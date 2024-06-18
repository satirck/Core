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