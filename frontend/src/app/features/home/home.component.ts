import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { toSignal } from '@angular/core/rxjs-interop';
import { OrganizationService } from '../../services/organization.service';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './home.component.html'
})
export class HomeComponent {
  private orgSvc = inject(OrganizationService);

  // convert observable -> readonly signal
  readonly orgs = toSignal(this.orgSvc.list(), { initialValue: [] });
}
