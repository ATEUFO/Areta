import { usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-gold text-aegean shadow-gold">
                <AppLogoIcon className="size-5 text-aegean" />
            </div>
            <div className="ml-2 grid flex-1 text-left">
                <span className="font-display text-base font-bold tracking-wider text-aegean dark:text-gold uppercase leading-none">
                    {name || 'ARETA'}
                </span>
                <span className="font-serif text-[10px] tracking-widest text-stone-500 uppercase">
                    Excellence & Suivi
                </span>
            </div>
        </>
    );
}

