define(["exports", "TYPO3/CMS/Core/DocumentService", "TYPO3/CMS/Iconpack/Backend/Iconpack"], (function(formEngineInput, DocumentService, Iconpack) {
  "use strict";
  return class {
    constructor(formEngineInput) {
      this.element = null;
      DocumentService.ready().then(() => {
        this.element = document.getElementById(formEngineInput);
        this.element.addEventListener("click", evt => {
          evt.preventDefault();
          Iconpack.showFieldIconModal(this.element);
        })
      })
    }
  }
}));
