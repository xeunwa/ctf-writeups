import { ApplicationConfig, importProvidersFrom, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { MonacoEditorModule, NgxMonacoEditorConfig, provideMonacoEditor } from 'ngx-monaco-editor-v2';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { providePrimeNG } from 'primeng/config';

import Aura from '@primeng/themes/aura';

export const monacoConfig: NgxMonacoEditorConfig = {
  baseUrl: window.location.origin + "/assets/monaco/min/vs",
};

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }), 
    provideRouter(routes), 
    importProvidersFrom(MonacoEditorModule.forRoot(monacoConfig)),
    providePrimeNG({
      theme: {
        preset: Aura
      }
    }),
    provideAnimationsAsync()
  ]
};

