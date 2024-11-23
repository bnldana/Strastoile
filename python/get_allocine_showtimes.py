#!/usr/bin/env python3
import sys
import json
from datetime import datetime, timedelta
from allocine_api import allocineAPI

def format_movie_data(movies_data, showtimes_data, day_shift_map):
    movies_dict = {}
    
    # Format movies data
    for movie in movies_data:
        # Garder la durée telle quelle si elle existe, sinon "Non spécifié"
        duration = movie.get('runtime', 'Non spécifié')
        if isinstance(duration, int):
            # Si c'est un entier (en secondes), le convertir en format "1h 21min"
            hours = duration // 3600
            minutes = (duration % 3600) // 60
            duration = f"{hours}h {minutes:02d}min"
        
        director = movie.get('director', '')
        if director and director != "Error" and isinstance(director, str):
            director = director.strip()
        else:
            director = "Non spécifié"

        movies_dict[movie['title']] = {
            'title': movie['title'],
            'director': director,
            'duration': duration,  # Utiliser la durée telle quelle
            'urlPoster': movie.get('urlPoster'),
            'releases': movie.get('releases', []),
            'showtimes': []
        }

    # Log raw showtimes for all movies
    print("\nDonnées brutes de l'API pour tous les films:", file=sys.stderr)
    for movie_title in movies_dict.keys():
        print(f"\nFilm : {movie_title}", file=sys.stderr)
        for showtime, day_shift in day_shift_map:
            if showtime['title'] == movie_title:
                print(f"Séances trouvées (Jour {day_shift}):", file=sys.stderr)
                for seance in showtime.get('showtimes', []):
                    base_date = datetime.now().date() + timedelta(days=day_shift)
                    time = datetime.fromisoformat(seance['startsAt']).time()
                    corrected_datetime = datetime.combine(base_date, time)
                    print(f"- Date brute: {seance.get('startsAt')} -> Date corrigée: {corrected_datetime.isoformat()}", file=sys.stderr)
    
    # Process showtimes with date correction
    for showtime, day_shift in day_shift_map:
        movie_title = showtime['title']
        if movie_title in movies_dict and 'showtimes' in showtime:
            base_date = datetime.now().date() + timedelta(days=day_shift)
            for seance in showtime['showtimes']:
                try:
                    time = datetime.fromisoformat(seance['startsAt']).time()
                    corrected_datetime = datetime.combine(base_date, time)
                    seance['startsAt'] = corrected_datetime.isoformat()
                except (ValueError, KeyError, AttributeError) as e:
                    print(f"Erreur de correction de date pour {movie_title}: {e}", file=sys.stderr)
                    continue
            
            movies_dict[movie_title]['showtimes'].extend(showtime['showtimes'])

    formatted_movies = []
    for movie_title, movie_data in movies_dict.items():
        showtimes_by_date = {}
        
        print(f"\nTraitement des séances pour {movie_title}:", file=sys.stderr)
        for showtime in movie_data['showtimes']:
            try:
                date = datetime.fromisoformat(showtime['startsAt'].split('T')[0])
                time = datetime.fromisoformat(showtime['startsAt']).strftime('%Hh%M')
                date_str = date.strftime('%Y-%m-%d')
                
                print(f"- Conversion: {showtime['startsAt']} -> date: {date_str}, heure: {time}", file=sys.stderr)
                
                if date_str not in showtimes_by_date:
                    showtimes_by_date[date_str] = set()
                showtimes_by_date[date_str].add(time)
            except (ValueError, KeyError, AttributeError) as e:
                print(f"Erreur de conversion pour {movie_title}: {e}", file=sys.stderr)
                continue

        formatted_showtimes = []
        for date, times in sorted(showtimes_by_date.items()):
            formatted_showtimes.append({
                'date': date,
                'horaires': sorted(list(times))
            })

        formatted_movie = {
            'film': movie_title,
            'director': movie_data['director'],
            'duration': movie_data['duration'],
            'genre': 'Non spécifié',
            'poster_url': movie_data['urlPoster'],
            'showtimes': formatted_showtimes
        }

        if movie_data['releases']:
            for release in movie_data['releases']:
                if release.get('releaseDate'):
                    try:
                        release_date = datetime.fromisoformat(release['releaseDate'].split('T')[0])
                        formatted_movie['release_date'] = release_date.strftime('%d/%m/%Y')
                        break
                    except (ValueError, AttributeError):
                        pass

        formatted_movies.append(formatted_movie)

    return formatted_movies
    movies_dict = {}
    
    # Format movies data
    for movie in movies_data:
        # Convertir runtime en entier s'il existe, sinon mettre 0
        runtime = int(movie.get('runtime', 0)) if movie.get('runtime') else 0

        # Conversion de la durée de secondes en format "XhYY"
        hours = runtime // 3600
        minutes = (runtime % 3600) // 60
        formatted_duration = f"{hours}h{minutes:02d}" if runtime > 0 else "Non spécifié"
        
        director = movie.get('director', '')
        if director and director != "Error" and isinstance(director, str):
            director = director.strip()
        else:
            director = "Non spécifié"

        movies_dict[movie['title']] = {
            'title': movie['title'],
            'director': director,
            'duration': runtime,
            'urlPoster': movie.get('urlPoster'),
            'releases': movie.get('releases', []),
            'showtimes': []
        }
    
    # Log raw showtimes for all movies
    print("\nDonnées brutes de l'API pour tous les films:", file=sys.stderr)
    for movie_title in movies_dict.keys():
        print(f"\nFilm : {movie_title}", file=sys.stderr)
        for showtime, day_shift in day_shift_map:
            if showtime['title'] == movie_title:
                print(f"Séances trouvées (Jour {day_shift}):", file=sys.stderr)
                for seance in showtime.get('showtimes', []):
                    base_date = datetime.now().date() + timedelta(days=day_shift)
                    time = datetime.fromisoformat(seance['startsAt']).time()
                    corrected_datetime = datetime.combine(base_date, time)
                    print(f"- Date brute: {seance.get('startsAt')} -> Date corrigée: {corrected_datetime.isoformat()}", file=sys.stderr)
    
    # Process showtimes with date correction
    for showtime, day_shift in day_shift_map:
        movie_title = showtime['title']
        if movie_title in movies_dict and 'showtimes' in showtime:
            base_date = datetime.now().date() + timedelta(days=day_shift)
            for seance in showtime['showtimes']:
                try:
                    time = datetime.fromisoformat(seance['startsAt']).time()
                    corrected_datetime = datetime.combine(base_date, time)
                    seance['startsAt'] = corrected_datetime.isoformat()
                except (ValueError, KeyError, AttributeError) as e:
                    print(f"Erreur de correction de date pour {movie_title}: {e}", file=sys.stderr)
                    continue
            
            movies_dict[movie_title]['showtimes'].extend(showtime['showtimes'])
    
    formatted_movies = []
    for movie_title, movie_data in movies_dict.items():
        showtimes_by_date = {}
        
        print(f"\nTraitement des séances pour {movie_title}:", file=sys.stderr)
        for showtime in movie_data['showtimes']:
            try:
                date = datetime.fromisoformat(showtime['startsAt'].split('T')[0])
                time = datetime.fromisoformat(showtime['startsAt']).strftime('%Hh%M')
                date_str = date.strftime('%Y-%m-%d')
                
                print(f"- Conversion: {showtime['startsAt']} -> date: {date_str}, heure: {time}", file=sys.stderr)
                
                if date_str not in showtimes_by_date:
                    showtimes_by_date[date_str] = set()
                showtimes_by_date[date_str].add(time)
            except (ValueError, KeyError, AttributeError) as e:
                print(f"Erreur de conversion pour {movie_title}: {e}", file=sys.stderr)
                continue

        print(f"\nDates finales après formatage pour {movie_title}:", file=sys.stderr)
        for date, times in sorted(showtimes_by_date.items()):
            print(f"- {date}: {sorted(list(times))}", file=sys.stderr)

        formatted_showtimes = []
        for date, times in sorted(showtimes_by_date.items()):
            formatted_showtimes.append({
                'date': date,
                'horaires': sorted(list(times))
            })

        formatted_movie = {
            'film': movie_title,
            'director': movie_data['director'],
            'duration': movie_data['duration'],
            'genre': 'Non spécifié',
            'poster_url': movie_data['urlPoster'],
            'showtimes': formatted_showtimes
        }

        if movie_data['releases']:
            for release in movie_data['releases']:
                if release.get('releaseDate'):
                    try:
                        release_date = datetime.fromisoformat(release['releaseDate'].split('T')[0])
                        formatted_movie['release_date'] = release_date.strftime('%d/%m/%Y')
                        break
                    except (ValueError, AttributeError):
                        pass

        formatted_movies.append(formatted_movie)
        
        # Debug final du JSON
        print(f"\nJSON final qui sera envoyé à PHP pour {movie_title}:", file=sys.stderr)
        print(json.dumps(formatted_movie, indent=2), file=sys.stderr)

    return formatted_movies

def get_cinema_data(cinema_id):
    api = allocineAPI()
    all_movies = []
    all_showtimes = []
    showtime_day_map = []  # Pour garder la trace du day_shift
    
    print(f"\nRécupération des données pour le cinéma {cinema_id}", file=sys.stderr)
    for day_shift in range(7):
        try:
            print(f"\nJour {day_shift}:", file=sys.stderr)
            
            movies = api.get_movies(cinema_id, day_shift=day_shift)
            print(f"- {len(movies)} films trouvés", file=sys.stderr)
            all_movies.extend(movies)
            
            showtimes = api.get_showtime(cinema_id, day_shift=day_shift)
            print(f"- {len(showtimes)} séances trouvées", file=sys.stderr)
            # Associe chaque séance avec son day_shift
            showtime_day_map.extend([(st, day_shift) for st in showtimes])
            all_showtimes.extend(showtimes)
            
        except Exception as e:
            print(f"Erreur pour le jour {day_shift}: {str(e)}", file=sys.stderr)
            continue
    
    formatted_data = format_movie_data(all_movies, all_showtimes, showtime_day_map)
    print(json.dumps(formatted_data))

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Usage: python get_allocine_showtimes.py <cinema_id>", file=sys.stderr)
        sys.exit(1)
    
    get_cinema_data(sys.argv[1])