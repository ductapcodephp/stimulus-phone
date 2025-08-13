import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["form", "quantity"];
    static outlets = ["cart"];
    connect() {
        this.productId = this.data.get('productId');
        this.apiUrl = `/addToCart/${this.productId}`;
    }

    showForm(event) {
        event.preventDefault();
        this.formTarget.style.display = this.formTarget.style.display === "block" ? "none" : "block";
    }

    async submitForm(event) {
        event.preventDefault();

        const quantity = this.quantityTarget.value || 1;

        try {
            const response = await fetch(this.apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ quantity: parseInt(quantity, 10) }),
            });

            if (!response.ok) {
                const error = await response.json();
                alert(error.message || "Có lỗi xảy ra");
                return;
            }

            const data = await response.json();

            this.formTarget.style.display = "none";
            if (this.hasCartOutlet) {
                this.cartOutlet.updateCartCount();
            }


        } catch (error) {
            alert("Không thể kết nối tới server");
            console.error(error);
        }
    }

}