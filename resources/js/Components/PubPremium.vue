<template>
    <div
        class="w-full flex items-center bg-mirage-900 justify-between flex-col sm:flex-row px-8 lg:px-14 2xl:px-[6.5rem] gap-6 py-14 my-6 relative border-y border-y-mirage-50/5">
        <div class="absolute left-0 hidden sm:block w-1/3 bg-mirage-900 z-10 top-0 bottom-0"></div>
        <div
            class="absolute left-1/3 hidden sm:block bg-gradient-to-l from-transparent to-mirage-900 z-10 top-0 bottom-0 right-0">
        </div>
        <video
            class="absolute opacity-10 sm:opacity-50 left-0 sm:left-1/3 top-0 bottom-0 w-safe-x-screen object-cover h-full z-1"
            autoplay muted loop playsinline src="/assets/max.t9V4hUG2.mp4">
        </video>
        <div class="flex flex-col gap-6 z-20 relative">
            <h1 class="text-3xl lg:text-5xl !leading-tight font-bold text-white text-center sm:text-left">Débloque le
                max<br>du streaming</h1>
            <p
                class="text-balance text-center sm:text-left col-start-1 row-start-3 max-w-3xl text-base leading-6 lg:text-lg text-primary-100 lg:leading-8">
                Les séries, les animés, les téléchargements et l’IPTV, tout inclus dans<br>un seul abonnement à
                9,99€/mois.</p>
        </div>
        <div class="flex flex-col items-center sm:items-end xl:flex-row gap-4 z-20 relative">
            <a class="" href="/subscription#advantages">
                <button
                    class="flex items-center justify-center font-medium whitespace-nowrap relative overflow-hidden transition h-12 px-4 xl:h-14 text-base xl:text-lg xl:px-6 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white ring-1 ring-white/10 ring-inset hover:from-primary-600 hover:to-primary-700 active:from-primary-700 active:to-primary-800 w-max gap-2"><svg
                        class="mdi-icon " width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M5 16L3 5L8.5 10L12 4L15.5 10L21 5L19 16H5M19 19C19 19.6 18.6 20 18 20H6C5.4 20 5 19.6 5 19V18H19V19Z">
                        </path>
                    </svg>Découvrir l'abonnement</button></a>
        </div>
    </div>

</template>