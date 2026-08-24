// assets/src/js/lightbox.js
// PhotoSwipe-Lightbox für Bilder mit "Bei Klick vergrößern" (siehe inc/lightbox.php).
import PhotoSwipeLightbox from "photoswipe-lightbox";

const containers = document.querySelectorAll(".entry-content");
if (containers.length) {
    const lightbox = new PhotoSwipeLightbox({
        gallery: Array.from(containers),
        children: "a.kk-lightbox",
        pswpModule: () => import("photoswipe"),

        bgOpacity: 1,
        padding: { top: 12, bottom: 12, left: 8, right: 8 },
        initialZoomLevel: "fit",
        secondaryZoomLevel: 2.5,
        maxZoomLevel: 5,
        wheelToZoom: true,

        closeTitle: "Schließen",
        zoomTitle: "Vergrößern",
        arrowPrevTitle: "Vorheriges Bild",
        arrowNextTitle: "Nächstes Bild",
        errorMsg: "Das Bild konnte nicht geladen werden.",
    });

    // Bildunterschrift aus dem <figcaption> des jeweiligen Bildes
    lightbox.on("uiRegister", () => {
        lightbox.pswp.ui.registerElement({
            name: "kk-caption",
            order: 9,
            isButton: false,
            appendTo: "root",
            onInit: (el, pswp) => {
                pswp.on("change", () => {
                    const link = pswp.currSlide?.data?.element;
                    const cap = link?.closest("figure")?.querySelector("figcaption");
                    const text = cap ? cap.textContent.trim() : "";
                    el.textContent = text;
                    el.hidden = !text;
                });
            },
        });
    });

    lightbox.init();
}
