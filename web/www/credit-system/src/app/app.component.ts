import { Component } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { filter } from 'rxjs/operators';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {

  onMainPage: boolean = true;

  constructor(private router: Router){
    this.onMainPage = this.router.url == '';

    //checks for navigation end- when the navigation to a new page has finished- to see what the route is
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe(() => {
        this.onMainPage = this.router.url == '';
      });
  }

}