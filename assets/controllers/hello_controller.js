import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static classes = [ "loading" ]
    loadResults() {

        this.element.classList.add(this.loadingClass)
        setTimeout(() => {
            this.element.classList.remove(this.loadingClass)
        }, 2000)
    }
}