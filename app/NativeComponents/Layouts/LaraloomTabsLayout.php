<?php

namespace App\NativeComponents\Layouts;

use App\Icons\Android;
use App\Icons\Ios;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class LaraloomTabsLayout extends NativeLayout
{
    public function usesNativeChrome(): bool
    {
        return true;
    }

    public function navBar(NativeComponent $screen): ?NavBar
    {
        $showBack = ! method_exists($screen, 'showsNavBack')
            || $screen->showsNavBack();

        if (! $showBack) {
            return null;
        }

        return NavBar::make()
            ->back()
            ->title($screen->navTitle());
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->activeColor('#F43F8C')
            ->minimizeOnScroll()
            ->add(Tab::link('Today', '/', ios: Ios::Newspaper, android: Android::Newspaper))
            ->add(Tab::link('Projects', '/projects', ios: Ios::Cube, android: Android::Apps))
            ->add(Tab::search(
                'Search',
                placeholder: 'Search Laravel today…',
                ios: Ios::Magnifyingglass,
                android: Android::Search,
            ))
            ->add(Tab::link('You', '/profile', ios: Ios::Person, android: Android::Person));
    }
}
