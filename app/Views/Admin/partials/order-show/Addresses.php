<aside class="orderAddressCard">
    <h2 class="orderAddressTitle">Addresses</h2>

    <div class="addressStack">
        <div class="addrCard">
            <div class="addrTitle">Shipping</div>
            <div class="addrBody"><?= nl2br(htmlspecialchars($shipping)) ?></div>
        </div>

        <div class="addrCard">
            <div class="addrTitle">Billing</div>
            <div class="addrBody"><?= nl2br(htmlspecialchars($billing)) ?></div>
        </div>
    </div>
</aside>
