import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { filter } from 'rxjs/operators';
import { CommonModule } from '@angular/common';
import { AuthService } from './auth.service';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, RouterLinkActive, CommonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit {

  onMainPage: boolean = true;

  constructor(private router: Router, public auth: AuthService) {
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event: NavigationEnd) => {
        this.onMainPage = event.url === '/';
        if (this.onMainPage && this.auth.isLoggedIn) {
          this.router.navigate(['/home']);
        }
      });
  }

  ngOnInit() {
    this.onMainPage = this.router.url === '/';
    if (this.onMainPage && this.auth.isLoggedIn) {
      this.router.navigate(['/home']);
    }
  }

}
