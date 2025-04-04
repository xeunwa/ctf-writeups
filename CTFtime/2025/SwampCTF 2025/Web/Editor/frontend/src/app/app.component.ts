import { Component, effect, ElementRef, model, ViewChild } from '@angular/core';
import { FormsModule } from '@angular/forms';

import { MonacoEditorModule } from 'ngx-monaco-editor-v2';
import { ButtonModule } from 'primeng/button';
import { DrawerModule } from 'primeng/drawer';

@Component({
  selector: 'app-root',
  imports: [
    FormsModule,
    MonacoEditorModule,
    ButtonModule,
    DrawerModule
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  @ViewChild('previewIframe') previewIframe!: ElementRef<HTMLIFrameElement>;
  
  protected cssEditorOptions = {theme: 'vs-dark', language: 'css', automaticLayout: true, links: false};
  protected htmlEditorOptions = {theme: 'vs-dark', language: 'html', automaticLayout: true, links: false};

  protected userCSS = model<string>(
`p {
	color: red;
}
`);
  protected userHTML = model<string>(
`<!DOCTYPE html>
<html>
	<head>
		<!--If you remove the below style tag, your CSS won't be applied.-->
		<style class=\'custom-user-css\'></style>
	</head>
	<body>
		<h1>My First Heading</h1>
		<p>My first paragraph.</p>
	</body>
</html>
`);

  constructor() {
    effect(() => this.updateRenderedPage(this.userHTML(), this.userCSS()));
  }

  ngAfterViewInit() {
	this.updateRenderedPage(this.userHTML(), this.userCSS());
  }

  private updateRenderedPage = (html: string, css: string) => {
	const content = html
		.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, "")
		.replace(/\son\w+="[^"]*"/gi, "")
		.replace(
			/<style class=['"]custom-user-css['"]><\/style>/,
    		`<style class='custom-user-css'>${css}</style>`
   		);

    const iframeDoc = this.previewIframe?.nativeElement.contentDocument!;
    iframeDoc?.open();
    iframeDoc?.write(content);
    iframeDoc?.close();
  }

}
