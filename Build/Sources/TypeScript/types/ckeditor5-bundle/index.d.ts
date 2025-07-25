declare namespace CKEDITOR {
  export interface pluginDefinition {
    icons?: string | string[];
  }
  export interface commandDefinition {
    allowedContent?: filter.allowedContentRules;
    requiredContent?: string | style;
  }
}

declare module '@ckeditor/ckeditor5-link' {
  export * from '@ckeditor/ckeditor5-link/src/index.js';
  export * as LinkUtils from '@ckeditor/ckeditor5-link/src/utils.js';
}
