import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["count"];

    connect() {
        this.updateCartCount();
    }

    updateCartCount() {
        fetch("/qtyCart", {
            method: "GET",
            headers: { Accept: "application/json" }
        })
            .then((res) => res.json())
            .then((data) => {
                if (this.hasCountTarget) {
                    this.countTarget.textContent = data.countItem;
                }
            })
            .catch((err) => console.error("Lỗi khi lấy số lượng giỏ hàng:", err));
    }

    increase(event) {
        const url = event.target.dataset.url;
        const id = event.target.dataset.id;
        this.updateQuantity(url, id);
    }

    decrease(event) {
        const url = event.target.dataset.url;
        const id = event.target.dataset.id;
        this.updateQuantity(url, id);
    }

    async updateQuantity(url, id) {
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            if (!response.ok) throw new Error("Lỗi khi cập nhật số lượng");

            const data = await response.json();

            const qtyEl = document.getElementById(`qty-${id}`);
            if (qtyEl) qtyEl.textContent = data.quantity;
            if (this.hasCountTarget) {
                this.countTarget.textContent = data.countItem;
            }
        } catch (error) {
            console.error("Error updating cart:", error);
        }
    }

}
