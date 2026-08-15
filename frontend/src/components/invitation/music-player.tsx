import { useEffect, useRef, useState } from "react";
import { Music as MusicIcon, Pause } from "lucide-react";
import { useInvitationData } from "./invitation-data-provider";

/**
 * Derives a playable embed URL for the platforms that don't allow direct
 * <audio src> playback — Spotify and YouTube both require their own iframe
 * player. Returns null if the stored link doesn't match a recognizable
 * track/video URL shape.
 */
function getEmbedUrl(source: "spotify" | "youtube_music", url: string): string | null {
  if (source === "spotify") {
    const match = url.match(/open\.spotify\.com\/(?:intl-\w+\/)?track\/([A-Za-z0-9]+)/);
    return match ? `https://open.spotify.com/embed/track/${match[1]}` : null;
  }

  const watchMatch = url.match(/[?&]v=([\w-]{11})/);
  const shortMatch = url.match(/youtu\.be\/([\w-]{11})/);
  const id = watchMatch?.[1] ?? shortMatch?.[1];
  return id ? `https://www.youtube.com/embed/${id}?autoplay=1&loop=1&playlist=${id}` : null;
}

export function MusicPlayer({ opened }: { opened: boolean }) {
  const invitation = useInvitationData();
  const music = invitation.music;
  const [playing, setPlaying] = useState(false);
  const audioRef = useRef<HTMLAudioElement | null>(null);

  // Browsers block audio autoplay-with-sound unless it follows a real user gesture — the
  // guest's "Buka Undangan" click is that gesture, so playback starts the moment `opened`
  // flips true rather than on initial mount (which would be silently blocked).
  useEffect(() => {
    if (!opened || !music || !music.autoplay) return;

    if (music.source === "upload") {
      audioRef.current
        ?.play()
        .then(() => setPlaying(true))
        .catch(() => setPlaying(false));
    } else {
      // Spotify/YouTube embeds autoplay via their own `autoplay=1` param once mounted.
      setPlaying(true);
    }
  }, [opened, music]);

  if (!music) return null;

  function toggle() {
    if (!music) return;

    if (music.source === "upload") {
      const audio = audioRef.current;
      if (!audio) return;
      if (playing) audio.pause();
      else void audio.play();
    }

    setPlaying((p) => !p);
  }

  const embedUrl = music.source !== "upload" ? getEmbedUrl(music.source, music.url) : null;

  return (
    <>
      {music.source === "upload" && <audio ref={audioRef} src={music.url} loop={music.loop} preload="auto" />}

      {embedUrl && playing && (
        <div
          className="fixed bottom-40 left-5 z-50 overflow-hidden rounded-xl border border-inv-border"
          style={{ width: 300, height: 80, boxShadow: "var(--inv-shadow)" }}
        >
          <iframe
            title="Pemutar musik latar"
            src={embedUrl}
            width="100%"
            height="100%"
            style={{ border: 0 }}
            allow="autoplay; encrypted-media"
          />
        </div>
      )}

      <button
        onClick={toggle}
        aria-label={playing ? "Matikan musik latar" : "Putar musik latar"}
        className="fixed bottom-24 left-5 z-50 grid size-12 place-items-center rounded-full border border-inv-border bg-inv-surface text-inv-primary transition-transform hover:scale-105"
        style={{ boxShadow: "var(--inv-shadow)" }}
      >
        {playing ? <Pause className="size-5" /> : <MusicIcon className="size-5" />}
        {playing && <span className="absolute inset-0 animate-ping rounded-full border border-inv-primary/40" />}
      </button>
    </>
  );
}
