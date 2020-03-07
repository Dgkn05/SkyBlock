<?php

namespace Can;

use pocketmine\network\protocol\DropItemPacket;
use pocketmine\{Player, Server};
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender, CommandExecutor, ConsoleCommandSender, PluginCommand};
use pocketmine\entity\{Entity, Effect};
use formapi\{ModalForm, CustomForm, SimpleForm};
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\event\block\{SignChangeEvent, BlockBreakEvent};
use pocketmine\utils\Config;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\generator\object\Tree;
use pocketmine\level\generator\Generator;
use pocketmine\block\Cobblestone;
use pocketmine\entity\Creature;
use pocketmine\level\format\io\BaseLevelProvider;

class SkyBlock extends PluginBase implements Listener
{

    public $b = "§aSky§2Block §8> ";
    public $oyuncu = array();
    public $cfg;


    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new SkyBlockTask($this), 20 * 15);
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "Adalar/");
        $this->getLogger()->info("SkyBlock Eklentisi Aktif!");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        $player = $sender->getPlayer();
        $dd = $sender->getName();
        $cs = $sender->getName();
        $oada = $this->getDataFolder() . "Adalar/" . $sender->getName() . ".yml";
        switch ($cmd->getName()) {
            case "ada":
                if (!file_exists($oada)) {
                    $this->oyuncuAda($player);
                } else {
                    $this->anadizin($player);
                }
                break;
            case "ortak":
                $this->ortakdizin($player);
                break;
            case "ortakekle":
                if (isset($args[0])) {
                    if ($args[0] == $player->getName()) {
                        return false;
                    }
                    $ortakekle = $this->getServer()->getPlayer($args[0]);
                    if ($ortakekle instanceof Player) {
                        $this->davetYolla($player, $ortakekle);
                    } else {
                        $player->sendMessage($this->b . "§cOyuncu bulunamadı!");
                    }
                } else {
                    $player->sendMessage($this->b . "§cOyuncu kısımına ortak ekleyeceğiniz oyuncunun ismini yazınız.");
                }
                break;
            case "ortakcikar":
                if (isset($args[0])) {
                    $ortak = $this->getServer()->getPlayer($args[0]);
                    if ($ortak instanceof Player or $ortak instanceof OfflinePlayer) {
                        $this->ortakKaldir($ortak, $player);
                    }
                } else {
                    $player->sendMessage($this->b . "§cOyuncu kısımına ortaklıktan çıkaracağınız oyuncunun ismini yazınız.");
                }
                break;
            case "cevapla":
                $this->ortakcevapla($player);
                break;
            case "oyuncutekmele":
                if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
                    $this->getServer()->loadLevel($player->getName());
                    $seviye = $this->getServer()->getLevelByName($player->getName());
                    if ($args[0] == "herkes") {
                        foreach ($seviye->getPlayers() as $oyuncular) {
                            if ($oyuncular->getName() != $player->getName()) {
                                $oyuncular->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn(), 0, 0);
                                $oyuncular->sendMessage($this->b . "§e" . $player->getName() . " §7isimli oyuncunun adasından atıldınız!");
                                $player->sendMessage($this->b . "§7Tüm oyuncular adanızdan atıldı!");
                            }
                        }
                    }
                    foreach ($seviye->getPlayers() as $hedef) {
                        if ($args[0] == $hedef->getName()) {
                            $hedef->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn(), 0, 0);
                            $hedef->sendMessage($this->b . "§e" . $player->getName() . " §7isimli oyuncunun adasından atıldınız!");
                            $player->sendMessage($this->b . "§e" . $hedef->getName() . " §7adlı oyuncu adanızdan atıldı!");
                        }
                    }
                } else {
                    $player->sendMessage($this->b . "§cAdanızdan oyuncu tekmelemek için önce bir ada oluşturmalısınız: '/ada'");
                }
                break;
        }
        return true;
    }


    public function anaDizin(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            $g = $player->getPlayer();
            $event = $player->getName();
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    break;
                case 1:
                    $this->oyuncuAdatp($player);
                    return;
                case 2:
                    if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
                        $adaconfig = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
                        if ($player->getLevel()->getFolderName() == $player->getName()) {
                            $adaconfig->set("X", $player->getX());
                            $adaconfig->set("Y", $player->getY());
                            $adaconfig->set("Z", $player->getZ());
                            $adaconfig->save();
                            $pos = new Vector3($player->getX(), $player->getY(), $player->getZ());
                            $player->getLevel()->setSpawnLocation($pos);
                            $player->sendMessage($this->b . " §aAda başlangıç noktası ayarlandı!");
                        } else {
                            $player->sendMessage($this->b . " §cBu özelliği kullanmak için önce adanıza gitmelisin!");
                        }
                    } else {
                        $player->sendMessage($this->b . " §cAda oluşturmadan başlangıç noktası ayarlayamazsın.");
                    }
                    return;
                case 3:
                    $this->ziyaret($player);
                    return;
                case 4:
                    $this->oyuncuTekmele($player);
                    return;
                case 5:
                    $this->ortakMenu($player);
                    break;
                case 8:
                    $this->adaSil($player);
                    return;
            }
        });
        $form->setTitle("SkyBlock");
        $form->setContent("Adana ışınlanarak oynamaya başlayabilirsiniz.");
        $form->addButton("§cKapat", 0);
        $form->addButton("Ada Işınlan", 1);
        $form->addButton("Başlangıç Ayarla", 2);
        $form->addButton("Ziyaret Et", 3);
        $form->addButton("Oyuncu Tekmele", 4);
        $form->addButton("Ortak Menü");
        $form->addButton("§cAda Sil", 5);
        $form->sendToPlayer($player);
    }

    public function ortakMenu(Player $player){
        $form = new SimpleForm(function (Player $player, $data){
            if($data === null){
                return;
            }
            switch($data){
                case 0:
                    $this->ortakEkle($player);
                    break;
                case 1:
                    $this->ortakCikar($player);
                    break;
                case 2:
                    $this->ortakliste($player);
                    break;
            }
        });
        $form->setTitle("Ortak Menü");
        $form->setContent("");
        $form->addButton("Ortak Ekle");
        $form->addButton("Ortak Çıkar");
        $form->addButton("Ortak Liste");
        $form->sendToPlayer($player);
    }

    public function ziyaret(Player $player)
    {
        $f = new CustomForm(function (Player $player, $data) {
            $s = $data[0];
            if ($s !== null) {
                if (file_exists($this->getDataFolder() . "$s.yml")) {
                    $cfg = new Config($this->getDataFolder() . "$s.yml", Config::YAML);
                    $player->teleport(new Vector3($cfg->get("x"), $cfg->get("Y"), $cfg->get("z")));
                    $player->sendMessage($this->b . "§e$s §aadlı oyuncu ziyaret ediliyor..");
                } else {
                    $player->sendMessage($this->b . "§cOyuncunun ziyaret tabelası bulunamadı.");
                }
            }
        });
        $f->setTitle("Ziyaret Menüsü");
        $f->addInput("Oyuncu ismi :", "Örnek;
 CanGunes74");
        $f->addLabel("§7Buradan ziyaret tabelası olan oyunculara ışınlanabilirsin.");
        $f->sendToPlayer($player);
    }

    public function ziyaretKirma(BlockBreakEvent $e)
    {
        $b = $e->getBlock();
        $o = $e->getPlayer();
        $d = $o->getLevel();
        $t = $d->getTile($b);
        $i = $o->getName();
        if ($t instanceof Sign) {
            $y = $t->getText();
            if ($y[0] == "§8[§bZiyaret§8]" && $y[1] == "§e" . $i) {
                if (file_exists($this->getDataFolder() . "$i.yml")) {
                    $dosya = $this->getDataFolder() . "$i.yml";
                    unlink($dosya);
                    $o->sendMessage($this->b . "§cZiyaret tabelası kaldırıldı.");
                }
            }
        }
    }

    public function tabelaOlusturma(SignChangeEvent $e)
    {
        if ($e->getLine(0) == "[ziyaret]") {
            $o = $e->getPlayer();
            $oi = $o->getName();
            $x = $e->getBlock()->getX();
            $y = $e->getBlock()->getY();
            $z = $e->getBlock()->getZ();
            $dunya = $o->getLevel()->getName();
            $cfg = new Config($this->getDataFolder() . "$oi.yml", Config::YAML);
            $cfg->set("x", $x);
            $cfg->set("Y", $y);
            $cfg->set("z", $z);
            $cfg->set("dunya", $dunya);
            $cfg->save();
            $e->setLine(0, "§8[§bZiyaret§8]");
            $e->setLine(1, "§e" . $e->getPlayer()->getName() . "");
            $o->sendMessage($this->b . "§aZiyaret tabelası oluşturuldu.");
        }
    }


    public function adaSil(Player $player){
        $form = new ModalForm(function (Player $player, $data){
            if(true === $data){
                $adac = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
                $zaman = time();
                $bugun = date('d.m.y', $zaman);
                $kurulusAda = $adac->get("Ada-Kurulus");
                $silBitis = $adac->get("Ada-Silebilecegi-Tarih");
                if ($bugun >= $silBitis) {
                    $oadakaydi = $this->getDataFolder() . "Adalar/" . $player->getName() . ".yml";
                    if (file_exists($oadakaydi)) {
                        $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn(), 0, 0);
                        unlink($oadakaydi);
                        $dizin = $this->getServer()->getDataPath() . "worlds/" . $player->getName();
                        system("rm -rf " . escapeshellarg($dizin));
                        $player->sendMessage($this->b . "§aAda kaydınız başarıyla silindi.");
                    } else {
                        $player->sendMessage($this->b . "§cOyuncu adanız olmadığı için silme işlemi yapılmadı!");
                    }
                } else {
                    $player->sendMessage($this->b . "§cAdanızı silebileceğiniz tarih: §7" . $silBitis);
                }
            }
        });
        $form->setTitle("Uyarı");
        $form->setContent("§cAdanızı Silerseniz Daha Geri Gelmez!\n\n §7Sorumluluk Sunucumuza Ait Değildir.");
        $form->setButton1("Evet");
        $form->setButton2("Hayır");
        $form->sendToPlayer($player);
    }

    public function oyuncuAdatp(Player $player)
    {
        $this->getServer()->loadLevel($player->getName());
        $dnya = $this->getServer()->getLevelByName($player->getName());
        $spawn = $dnya->getSafeSpawn();
        $player->teleport($spawn, 0, 0);
        $player->teleport(new Vector3($spawn->getX(), $spawn->getY(), $spawn->getZ()));
        $player->sendMessage($this->b . "§aAdanıza ışınlandınız!");
    }

    public function oyuncuAda(Player $player)
    {
        $player->sendMessage($this->b . "§7Adanız oluşturulmaya başladı lütfen oyundan ayrılmayın.");
        $adac = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
        $adac->set("X", 130);
        $adac->set("Y", 50);
        $adac->set("Z", 128);
        $adac->set("Dunya", $player->getName());
        $adac->set("Ziyaret", "kapali");
        $adac->save();

        $zaman = time();
        $bugun = date('d.m.y', $zaman);
        $gun5 = strtotime('+5 day');
        $bitis = date('d.m.y', $gun5);
        $adac->set("Ada-Kurulus", $bugun);
        $adac->set("Ada-Silebilecegi-Tarih", $bitis);
        $adac->save();
        $silC = $adac->get("Ada-Silebilecegi-Tarih");

        $this->dosyaKopyala($player);
        $this->getServer()->loadLevel($player->getName());
        $dnya = $this->getServer()->getLevelByName($player->getName());
        $spawn = $dnya->getSafeSpawn();
        $player->teleport($spawn, 0, 0);
        $player->getLevel()->populateChunk($player->getFloorX() >> 4, $player->getFloorZ() >> 4, true);
        $player->getInventory()->addItem(Item::get(362, 0, 1));
        $player->getInventory()->addItem(Item::get(361, 0, 1));
        $player->getInventory()->addItem(Item::get(81, 0, 4));
        $player->getInventory()->addItem(Item::get(12, 0, 4));
        $player->getInventory()->addItem(Item::get(10, 0, 1));
        $player->getInventory()->addItem(Item::get(8, 0, 2));
        $player->getInventory()->addItem(Item::get(54, 0, 1));
        $player->getInventory()->addItem(Item::get(325, 0, 1));
        $player->sendMessage($this->b . "§7Adanız oluşturuldu!");
        $player->sendMessage("§eAdanızı silebileceğiniz tarih: §7" . $silC);
        $this->getServer()->getCommandMap()->dispatch($player->getPlayer(), "fix");
    }

    public function dosyaKopyala(Player $player)
    {
        $sd = $this->getServer()->getDataPath();
        @mkdir($sd . "worlds/" . $player->getName() . "/");
        @mkdir($sd . "worlds/" . $player->getName() . "/region/");
        $dunya = opendir($this->getServer()->getDataPath() . "SB/region/");
        while ($dosya = readdir($dunya)) {
            if ($dosya != "." and $dosya != "..") {
                copy($sd . "SB/region/" . $dosya, $sd . "worlds/" . $player->getName() . "/region/" . $dosya);
            }
        }

        copy($sd . "SB/level.dat", $sd . "worlds/" . $player->getName() . "/level.dat");
    }

    public function oyuncuTekmele($player)
    {
        if ($player instanceof Player) {
            $form = new CustomForm(function (Player $event, $data) {
                $result = $data[0];
                if ($result != null) {
                    $kod = "oyuncutekmele " . $result;
                    $this->getServer()->getCommandMap()->dispatch($event->getPlayer(), $kod);

                }
            });
            $form->setTitle("Oyuncu Tekmele Menüsü");
            $form->addInput("Oyuncu Adı", "Örnek; CanGunes74");
            $form->sendToPlayer($player);
        } else {
            $player->sendMessage($this->b . "§cOyuncu bulunamadı.");
        }
    }

    public function ortakEkle(Player $player)
    {
        if ($player instanceof Player) {
            $form = new CustomForm(function (Player $event, $data) {
                $result = $data[0];
                if ($result != null) {
                    $kod = "ortakekle " . $result;
                    $this->getServer()->getCommandMap()->dispatch($event->getPlayer(), $kod);

                }
            });
            $form->setTitle("Ortak Ekle");
            $form->addInput("Oyuncu", "CanGunes74");
            $form->sendToPlayer($player);
        } else {
            $player->sendMessage($this->b . "§cOyuncu bulunamadı.");
        }
    }

    public function ortakCikar(Player $player)
    {
        if ($player instanceof Player) {
            $form = new CustomForm(function (Player $event, $data) {
                $result = $data[0];
                if ($result != null) {
                    $kod = "ortakcikar " . $result;
                    $this->getServer()->getCommandMap()->dispatch($event->getPlayer(), $kod);

                }
            });
            $form->setTitle("Ortak Çıkart");
            $form->addInput("Oyuncu", "CanGunes74");
            $form->sendToPlayer($player);
        } else {
            $player->sendMessage($this->b . "§cOyuncu bulunamadı.");
        }
    }

    public function davetYolla($player, $ortakekle)
    {
        if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
            $this->player[$ortakekle->getName()] = $player->getName();
            $this->ortakcevapla($ortakekle, $player);
        } else {
            $player->sendMessage($this->b . "§7Önce bir ada oluşturmalısınız.!");
        }
    }
    
    public function ortakCevapla(Player $player){
        $form = new ModalForm(function (Player $player, $data){
            if(true === $data){
                $hedef = $this->player[$player->getName()];
                if ($hedef) {
                    $this->ortakEklee($player->getName(), $hedef);
                    unset($this->player[$player->getName()]);
                } else {
                    $player->sendMessage($this->b . "§7Şuan bir ortak daveti yok!");
                }
            }elseif(false === $data){
                $hedef = $this->player[$player->getName()];
                $h = $this->getServer()->getPlayer($hedef);
                if ($hedef) {
                    unset($this->player[$player->getName()]);
                    $h->sendMessage($this->b . "§f" . $player->getName() . " §coyuncusu ortaklığı reddetti!");
                } else {
                    $player->sendMessage($this->b . "§7Şuan bir ortak daveti yok!");
                }
            }
            return true;
        });
        $form->setTitle("Uyarı");
        $form->setContent("Ortaklık isteğini kabul ederseniz vereceğiniz yetkiler;\n\n§71- Adanıza ışınlanabilecek\n§72- Adanızda blok koyup kırabilecek");
        $form->setButton1("Evet");
        $form->setButton2("Hayır");
    }

    // Ortak Ekleme

    public function ortakEklee($ortak, $player)
    {
        $hedef = $this->getServer()->getPlayer($ortak);
        $player = $this->getServer()->getPlayer($player);
        if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
            if ($hedef instanceof Player) {
                $ekc = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
                $ekc->reload();
                if (empty($ekc->get("Ortaklar"))) {
                    $ekc->set("Ortaklar", array($hedef->getName()));
                    $ekc->save();
                    $player->sendMessage($this->b . $hedef->getName() . " §aadlı oyuncu ortak olarak eklendi !");
                    $hedef->sendMessage($this->b . $player->getName() . " §aoyuncusu sizi ortak olarak ekledi !");
                } else {
                    $orlar = $ekc->get("Ortaklar");
                    $iy = implode(" ", $orlar);
                    $ekc->set("Ortaklar", explode(" ", $iy . " " . $hedef->getName()));
                    $ekc->save();
                    $player->sendMessage($this->b . $hedef->getName() . " §aadlı oyuncu ortak olarak eklendi !");
                    $hedef->sendMessage($this->b . $player->getName() . " §aoyuncusu sizi ortak olarak ekledi !");
                }
            } else {
                $player->sendMessage($this->b . " §cOyuncu bulunamadı!");
            }
        } else {
            $player->sendMessage($this->b . " §cHenüz bir adan yok!");
        }
    }

    // Ortak Kaldırma

    public function ortakKaldir($ortak, $player)
    {
        if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
            $ocikar = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
            if ($ocikar->get("Ortaklar")) {
                $iy = $ocikar->get("Ortaklar");
                if (in_array($ortak->getName(), $iy)) {
                    $deger = array_search($ortak->getName(), $iy);
                    unset($iy[$deger]);
                    $ocikar->set("Ortaklar", $iy);
                    $ocikar->save();
                    $player->sendMessage($this->b . $ortak->getName() . " §coyuncusu ortaklıktan çıkarıldı!");
                    if ($ortak instanceof Player) {
                        $ortak->sendMessage($this->b . $player->getName() . " §csizi ortaklıktan çıkardı!");
                    } else {
                        $player->sendMessage($this->b . $ortak->getName() . " §cadında ortak bulunamadı!");
                    }
                } else {
                    $player->sendMessage($this->b . $ortak->getName() . " §cadında ortak bulunamadı!");
                }
            } else {
                $player->sendMessage($this->b . "§cHiç ortağın yok!");
            }
        } else {
            $player->sendMessage($this->b . "§cHenüz bir adan yok!");
        }
    }

    // Ortak Liste Kodları

    public function ortakliste(Player $player)
    {
        if (file_exists($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml")) {
            $oc = new Config($this->getDataFolder() . "Adalar/" . $player->getName() . ".yml", Config::YAML);
            $orll = $oc->get("Ortaklar");
            if ($orll) {
                $orrr = null;
                foreach ($orll as $orl) {
                    $orrr .= "\n§7 $orl";
                }
                $player->sendMessage($this->b . "§eOrtakların: §7$orrr");
            } else {
                $player->sendMessage($this->b . "§cŞu anda bir ortağın yok!");
            }
        } else {
            $player->sendMessage($this->b . "§cHenüz bir adan yok!");
        }
    }
    
} 