<form id="rgb-users-table-form">
    <div class="rgb-users-table-filter-by-role">
        <label for="rgb-users-table-role"><?= __("Filter by role", "rgbcode")?></label>
        <select name="role" id="rgb-users-table-role">
            <option value=""><?= __("All", "rgbcode")?></option>
            <?php foreach ($roles as $value => $role) { ?>
                <option value="<?= $value ?>"><?= $role['name'] ?></option>
            <?php } ?>
        </select>
    </div>
    <table class="rgb-users-table">
        <thead>
        <tr>
            <th class="rgb-users-table-change-orderby" data-orderby="user_login"><?= __("Username", "rgbcode")?> <i class="fa-solid fa-sort"></i></th>
            <th class="rgb-users-table-change-orderby" data-orderby="display_name"><?= __("Display Name", "rgbcode")?> <i class="fa-solid fa-sort"></i></th>
            <th><?= __("Role", "rgbcode")?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user) { ?>
            <tr>
                <td>
                    <?= esc_html($user->user_login) ?>
                </td>
                <td>
                    <?= esc_html($user->display_name) ?>
                </td>
                <td>
                    <?= esc_html($user->roles[0]) ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <input type="hidden" name="per_page" class="rgb-users-table-per-page" value="<?= $args['per_page'] ?>">
    <input type="hidden" name="order" class="rgb-users-table-order" value="<?= $args['order'] ?>">
    <input type="hidden" name="orderby" class="rgb-users-table-orderby" value="<?= $args['orderby'] ?>">
    <input type="hidden" name="page" class="rgb-users-table-page" value="1">

    <div class="rgb-users-table-pagination"></div>
</form>