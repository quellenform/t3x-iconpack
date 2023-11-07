/**
 * Currently a mixture between namespace and global object
 * Add types as you use them
 */
declare namespace TYPO3 {
  export const lang: {
    [key: string]: string
  };
  export namespace settings {
    export const ajaxUrls: {
      [key: string]: string
    };
    export const cssUrls: {
      [key: string]: string
    };
    export namespace FormEngine {
      export const moduleUrl: string;
      export const formName: string;
      export const legacyFieldChangedCb: () => void;
    }
  }
}

/**
 * Declare modules for dependencies without TypeScript declarations for v11
 */
declare module 'TYPO3/CMS/Backend/ActionButton/DeferredAction';
declare module 'TYPO3/CMS/Backend/FormEngine';
declare module 'TYPO3/CMS/Backend/FormEngineValidation';
declare module 'TYPO3/CMS/Backend/Icons';
declare module 'TYPO3/CMS/Backend/Modal';
declare module 'TYPO3/CMS/Core/Ajax/AjaxRequest';
declare module 'TYPO3/CMS/Core/Ajax/AjaxResponse';
declare module 'TYPO3/CMS/Core/DocumentService';

/**
 * Declare modules for dependencies without TypeScript declarations for v12
 */
declare module '@typo3/backend/action-button/deferred-action.js';
declare module '@typo3/backend/form-engine-validation.js';
declare module '@typo3/backend/form-engine.js';
declare module '@typo3/backend/icons.js';
declare module '@typo3/backend/modal.js';
declare module '@typo3/core/ajax/ajax-request.js';
declare module '@typo3/core/ajax/ajax-response.js';
declare module '@typo3/core/document-service.js';
