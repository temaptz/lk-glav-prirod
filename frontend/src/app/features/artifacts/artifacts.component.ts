import { Component, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { toSignal } from '@angular/core/rxjs-interop';
import { ArtifactService, ArtifactDto } from '../../services/artifact.service';
import { OrganizationService } from '../../services/organization.service';

@Component({
  selector: 'app-artifacts',
  standalone: true,
  imports: [CommonModule, FormsModule],
  template: `
    <h3>Артефакты / Хранилище</h3>

    <label>Организация:
      <select [(ngModel)]="orgId" (change)="load()" name="org">
        <option *ngFor="let o of orgs()" [value]="o.id">{{ o.name }}</option>
      </select>
    </label>

    <div *ngIf="orgId">
      <h4>Загрузить документ</h4>
      <input type="file" (change)="onFileSelect($event)" />
      <button (click)="uploadFile()" [disabled]="!selectedFile()">Загрузить</button>
    </div>

    <h4>Список документов</h4>
    <ul>
      <li *ngFor="let a of artifacts()">
        {{ a.filename || a.path }} - <a [href]="a.url" target="_blank">Скачать</a>
      </li>
    </ul>
  `
})
export class ArtifactsComponent {
  private orgSvc = inject(OrganizationService);
  private artSvc = inject(ArtifactService);

  orgs = toSignal(this.orgSvc.list(), { initialValue: [] });
  artifacts = signal<ArtifactDto[]>([]);
  selectedFile = signal<string | null>(null);
  selectedFilename = signal<string | null>(null);
  orgId: number | null = null;

  load() {
    if (!this.orgId) return;
    this.artSvc.list(this.orgId).subscribe(res => this.artifacts.set(res));
  }

  onFileSelect(event: any) {
    const file = event.target.files[0];
    if (!file) {
      this.selectedFile.set(null);
      this.selectedFilename.set(null);
      return;
    }
    
    this.selectedFilename.set(file.name);
    
    const reader = new FileReader();
    reader.onload = () => {
      const base64 = (reader.result as string).split(',')[1];
      this.selectedFile.set(base64);
    };
    reader.readAsDataURL(file);
  }

  uploadFile() {
    if (!this.orgId || !this.selectedFile() || !this.selectedFilename()) return;
    
    this.artSvc.upload(this.orgId, this.selectedFile()!, this.selectedFilename()!).subscribe(() => {
      this.selectedFile.set(null);
      this.selectedFilename.set(null);
      this.load();
    });
  }
}
