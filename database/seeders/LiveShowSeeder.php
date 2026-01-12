<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LiveShow;
use App\Models\LiveShowQuiz;
use App\Models\QuizOption as LiveShowQuizOption;

use Illuminate\Support\Str;
use Carbon\Carbon;

class LiveShowSeeder extends Seeder
{
    public function run(): void
    {
        $topics = ['General Knowledge', 'Science Basics', 'World Geography', 'Language Guessing', 'Brands & Logos', 'Technology', 'Sports', 'Movies & Entertainment', 'Animals & Nature', 'History & Culture'];

        $quizData = [

            'General Knowledge' => [
                ['What is the capital city of Canada?', ['Toronto', 'Vancouver', 'Ottawa', 'Montreal'], 2],
                ['Which sport uses a hoop and a ball?', ['Baseball', 'Basketball', 'Cricket', 'Soccer'], 1],
                ['What do bees produce?', ['Milk', 'Honey', 'Silk', 'Bread'], 1],
                ['Which item tells time?', ['Calendar', 'Clock', 'Compass', 'Scale'], 1],
                ['What color do you get by mixing red and white?', ['Pink', 'Green', 'Blue', 'Yellow'], 0],
                ['Which sense do humans use to taste food?', ['Hearing', 'Sight', 'Touch', 'Taste'], 3],
                ['How many legs does a spider have?', ['6', '8', '4', '10'], 1],
                ['What do you call a baby dog?', ['Puppy', 'Kitten', 'Cub', 'Foal'], 0],
                ['Which shape has three sides?', ['Square', 'Circle', 'Triangle', 'Rectangle'], 2],
                ['Which day comes after Friday?', ['Sunday', 'Saturday', 'Thursday', 'Monday'], 1],
            ],

            'Science Basics' => [
                ['What is the chemical symbol for water?', ['H2O', 'O2', 'CO2', 'NaCl'], 0],
                ['What planet is closest to the sun?', ['Venus', 'Earth', 'Mercury', 'Mars'], 2],
                ['What part of a plant makes food using sunlight?', ['Stem', 'Root', 'Leaf', 'Flower'], 2],
                ['What do you call molten rock from a volcano?', ['Ash', 'Magma', 'Ice', 'Rain'], 1],
                ['What is the force that keeps us on the ground?', ['Gravity', 'Friction', 'Magnetism', 'Electricity'], 0],
                ['Which gas do humans exhale?', ['Oxygen', 'Nitrogen', 'Carbon dioxide', 'Helium'], 2],
                ['What is the center of an atom called?', ['Electron', 'Proton', 'Nucleus', 'Molecule'], 2],
                ['Which organ processes food in the body?', ['Kidney', 'Liver', 'Brain', 'Stomach'], 3],
                ['Which state of matter has a fixed volume but not fixed shape?', ['Gas', 'Liquid', 'Plasma', 'Solid'], 1],
                ['Which body system pumps blood?', ['Digestive', 'Circulatory', 'Nervous', 'Respiratory'], 1],
            ],

            'World Geography' => [
                ['Which continent is known as the “Dark Continent”?', ['Asia', 'Africa', 'Europe', 'Antarctica'], 1],
                ['What is the largest ocean on Earth?', ['Atlantic', 'Indian', 'Arctic', 'Pacific'], 3],
                ['Which country is shaped like a boot?', ['Spain', 'Italy', 'France', 'Greece'], 1],
                ['What is the capital of Australia?', ['Sydney', 'Canberra', 'Melbourne', 'Perth'], 1],
                ['Which river is the longest in the world?', ['Yangtze', 'Mississippi', 'Nile', 'Amazon'], 2],
                ['Mount Fuji is in which country?', ['China', 'Japan', 'South Korea', 'Thailand'], 1],
                ['Which desert is the hottest on Earth?', ['Gobi', 'Sahara', 'Kalahari', 'Arctic'], 1],
                ['Which U.S. state is known as the “Sunshine State”?', ['California', 'Florida', 'Texas', 'Arizona'], 1],
                ['What is the smallest country in the world?', ['Monaco', 'Vatican City', 'San Marino', 'Liechtenstein'], 1],
                ['Which sea lies between Europe and Africa?', ['Red Sea', 'Arabian Sea', 'Mediterranean Sea', 'Black Sea'], 2],
            ],

            'Language Guessing' => [
                ['What language is primarily spoken in Brazil?', ['Spanish', 'Portuguese', 'French', 'English'], 1],
                ['“Bonjour” is a greeting in which language?', ['Spanish', 'German', 'French', 'Italian'], 2],
                ['“Arigato” means thank you in which language?', ['Chinese', 'Japanese', 'Korean', 'Thai'], 1],
                ['Which language uses the Cyrillic alphabet?', ['Russian', 'English', 'Arabic', 'Hindi'], 0],
                ['“Hola” is from which language?', ['Portuguese', 'Spanish', 'Italian', 'English'], 1],
                ['Which language do they speak in Egypt?', ['Arabic', 'Hebrew', 'English', 'French'], 0],
                ['“Ciao” is commonly used in which country?', ['France', 'Italy', 'Mexico', 'Japan'], 1],
                ['Which language has characters called “hanzi”?', ['Japanese', 'Korean', 'Chinese', 'Thai'], 2],
                ['“Danke” means thank you in which language?', ['Spanish', 'German', 'Dutch', 'Swedish'], 1],
                ['Which language is official in Argentina?', ['Portuguese', 'Spanish', 'English', 'German'], 1],
            ],

            'Brands & Logos' => [
                ['Which company uses an apple logo?', ['Samsung', 'Apple', 'Microsoft', 'Google'], 1],
                ['Which brand has a swoosh logo?', ['Nike', 'Adidas', 'Puma', 'Reebok'], 0],
                ['Which company has a blue bird logo?', ['Facebook', 'Twitter', 'Snapchat', 'LinkedIn'], 1],
                ['Which brand is known for fast-food burgers?', ['Subway', 'Burger King', 'Taco Bell', 'Domino’s'], 1],
                ['Which car company uses four rings logo?', ['BMW', 'Audi', 'Toyota', 'Honda'], 1],
                ['Which brand makes the “PlayStation”?', ['Microsoft', 'Sony', 'Nintendo', 'Google'], 1],
                ['Which logo has a mermaid figure?', ['Starbucks', 'Costa Coffee', 'Dunkin', 'Tim Hortons'], 0],
                ['Which company owns YouTube?', ['Amazon', 'Meta', 'Google', 'Apple'], 2],
                ['Which brand is famous for premium sports shoes and apparel?', ['Puma', 'Nike', 'Vans', 'Converse'], 1],
                ['Which company is known for the “Think Different” slogan?', ['IBM', 'Apple', 'HP', 'Dell'], 1],
            ],

            'Technology' => [
                ['What does “HTTP” stand for?', ['HyperText Transfer Protocol', 'High Transfer Text Protocol', 'HyperTech Text Process', 'HighText Transfer Program'], 0],
                ['What technology is used to mine Bitcoin?', ['AI', 'Blockchain', 'Cloud', 'VPN'], 1],
                ['What does “AI” stand for?', ['Automatic Intelligence', 'Artificial Integration', 'Artificial Intelligence', 'Automated Input'], 2],
                ['Which device do you use to point and click?', ['Keyboard', 'Mouse', 'Monitor', 'Printer'], 1],
                ['What is the main memory in a computer called?', ['HDD', 'RAM', 'USB', 'GPU'], 1],
                ['What technology do smartphones primarily use for calls?', ['Bluetooth', 'Wi-Fi', 'Cellular Network', 'GPS'], 2],
                ['What does “USB” stand for?', ['Universal Serial Bus', 'Universal System Backup', 'United Serial Bus', 'User System Bus'], 0],
                ['Which company developed the Android OS?', ['Apple', 'Google', 'Microsoft', 'Samsung'], 1],
                ['Which device displays text and visuals?', ['Monitor', 'CPU', 'Modem', 'Router'], 0],
                ['What does “AI” enhance in tech?', ['Heat Transfer', 'Automation', 'Intelligence', 'Color Display'], 2],
            ],

            'Sports' => [
                ['How many players are on a soccer team on the field?', ['9', '11', '10', '12'], 1],
                ['What sport uses a racket and shuttlecock?', ['Tennis', 'Badminton', 'Squash', 'Ping Pong'], 1],
                ['Which country hosted the 2016 Summer Olympics?', ['China', 'UK', 'Brazil', 'Japan'], 2],
                ['In which sport is a “home run” scored?', ['Cricket', 'Baseball', 'Football', 'Rugby'], 1],
                ['Which sport uses a puck?', ['Basketball', 'Ice Hockey', 'Tennis', 'Golf'], 1],
                ['How many points is a touchdown worth in American Football?', ['5', '6', '3', '7'], 1],
                ['Which sport is played on ice with sticks?', ['Soccer', 'Ice Hockey', 'Basketball', 'Field Hockey'], 1],
                ['What is the term for a score of zero in tennis?', ['Love', 'Zero', 'Null', 'None'], 0],
                ['In which sport do players wear gloves to box?', ['Wrestling', 'Boxing', 'Judo', 'Fencing'], 1],
                ['Which sport uses a longboard and waves?', ['Surfing', 'Skating', 'Skiing', 'Cycling'], 0],
            ],

            'Movies & Entertainment' => [
                ['Which movie features a character named “Jack Sparrow”?', ['Avatar', 'Titanic', 'Pirates of the Caribbean', 'Star Wars'], 2],
                ['Who is known as the “King of Pop”?', ['Elvis Presley', 'Michael Jackson', 'Prince', 'Justin Timberlake'], 1],
                ['Which movie has a wizard school called Hogwarts?', ['Lord of the Rings', 'Harry Potter', 'Twilight', 'Percy Jackson'], 1],
                ['Which character says “I am Iron Man”?', ['Thor', 'Captain America', 'Iron Man', 'Hulk'], 2],
                ['Which animated movie features talking toys?', ['The Lion King', 'Toy Story', 'Cars', 'Frozen'], 1],
                ['Who directed “Jurassic Park”?', ['James Cameron', 'Steven Spielberg', 'Christopher Nolan', 'Peter Jackson'], 1],
                ['Which TV show is about a group of friends in New York?', ['How I Met Your Mother', 'Friends', 'Seinfeld', 'Big Bang Theory'], 1],
                ['What is the highest-grossing film series of all time?', ['Harry Potter', 'Marvel Cinematic Universe', 'Star Wars', 'James Bond'], 1],
                ['Which movie features a superhero from Gotham City?', ['Superman', 'Batman', 'Spider-Man', 'Iron Man'], 1],
                ['Who sang “Let It Go” in “Frozen”?', ['Idina Menzel', 'Demi Lovato', 'Ariana Grande', 'Selena Gomez'], 0],
            ],

            'Animals & Nature' => [
                ['Which animal is known as the King of the Jungle?', ['Lion', 'Tiger', 'Elephant', 'Giraffe'], 0],
                ['What do you call a group of wolves?', ['Pack', 'Herd', 'Flock', 'School'], 0],
                ['Which bird is known for its colorful tail feathers?', ['Penguin', 'Peacock', 'Eagle', 'Swan'], 1],
                ['What is the tallest land animal?', ['Elephant', 'Giraffe', 'Rhino', 'Hippo'], 1],
                ['Which animal is famous for rolling in mud to cool off?', ['Pig', 'Lion', 'Bear', 'Rabbit'], 0],
                ['What gas do plants absorb for photosynthesis?', ['Oxygen', 'Carbon dioxide', 'Nitrogen', 'Hydrogen'], 1],
                ['Which ocean creature has eight arms?', ['Shark', 'Octopus', 'Dolphin', 'Whale'], 1],
                ['What is a baby cat called?', ['Kid', 'Calf', 'Kitten', 'Puppy'], 2],
                ['Which animal is the largest mammal?', ['Elephant', 'Blue Whale', 'Great White Shark', 'Giraffe'], 1],
                ['What do bees collect from flowers?', ['Pollen', 'Leaves', 'Water', 'Seeds'], 0],
            ],

            'History & Culture' => [
                ['Who was the first President of the United States?', ['John Adams', 'Thomas Jefferson', 'George Washington', 'Abraham Lincoln'], 2],
                ['Which ancient wonder was located in Egypt?', ['Hanging Gardens', 'Statue of Zeus', 'Great Pyramid of Giza', 'Colossus of Rhodes'], 2],
                ['In which year did World War II end?', ['1945', '1939', '1918', '1963'], 0],
                ['Who painted the Mona Lisa?', ['Van Gogh', 'Pablo Picasso', 'Leonardo da Vinci', 'Claude Monet'], 2],
                ['What wall fell in 1989 symbolizing end of the Cold War?', ['Great Wall', 'Berlin Wall', 'Hadrian’s Wall', 'Western Wall'], 1],
                ['Which empire built the Colosseum?', ['Greek', 'Roman', 'Ottoman', 'Persian'], 1],
                ['What holiday commemorates the end of slavery in the U.S.?', ['Thanksgiving', 'Juneteenth', 'Labor Day', 'Memorial Day'], 1],
                ['Who was known as the Maid of Orleans?', ['Queen Elizabeth I', 'Joan of Arc', 'Cleopatra', 'Marie Curie'], 1],
                ['Which ancient city was buried by a volcano in AD 79?', ['Rome', 'Athens', 'Pompeii', 'Carthage'], 2],
                ['What is the traditional Japanese form of poetry with 17 syllables?', ['Haiku', 'Sonnet', 'Limerick', 'Couplet'], 0],
            ],

        ];


        // Create 10 shows
        foreach (range(0, 9) as $i) {
            $topic = $topics[$i];
            $title = $topic . ' Live Show';
            $status = ['live', 'scheduled', 'completed'][array_rand(['live', 'scheduled', 'completed'])];

            $liveShow = LiveShow::create([
                'title'        => $title,
                'description'  => 'Interactive quiz session covering ' . strtolower($topic) . '.',
                'scheduled_at' => Carbon::now()->addDays($i)->setTime(18, 0),
                'status'       => $status,
                'thumbnail'    => 'https://picsum.photos/seed/' . Str::slug($topic) . '/400/250',

                'host_name'    => fake()->name(),
                'prize_amount' => rand(50, 500),
                'currency'     => 'EUR',
                'created_by'   => 1, // adjust for your admin user
            ]);

            foreach ($quizData[$topic] as [$question, $options, $correctIndex]) {
                $quiz = LiveShowQuiz::create([
                    'live_show_id' => $liveShow->id,
                    'question'     => $question,
                ]);

                foreach ($options as $idx => $opt) {
                    LiveShowQuizOption::create([
                        'quiz_id' => $quiz->id,
                        'option_text'       => $opt,
                        'is_correct'        => $idx === $correctIndex ? 1 : 0,
                    ]);
                }
            }
        }
    }
}
