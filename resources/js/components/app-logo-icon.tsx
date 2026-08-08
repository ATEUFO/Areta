import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* Fronton grec / Delta Aretè */}
            <path d="M16 3L3 11H29L16 3Z" fill="currentColor" />
            <rect x="5" y="11" width="22" height="2" fill="currentColor" opacity="0.8" />
            {/* Trois colonnes doriques */}
            <rect x="7" y="15" width="3" height="11" rx="0.5" fill="currentColor" />
            <rect x="14.5" y="15" width="3" height="11" rx="0.5" fill="currentColor" />
            <rect x="22" y="15" width="3" height="11" rx="0.5" fill="currentColor" />
            {/* Stylobate (base) */}
            <rect x="4" y="27" width="24" height="2" fill="currentColor" />
        </svg>
    );
}

