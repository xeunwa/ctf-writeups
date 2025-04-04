import { PrismaClient } from '@prisma/client';
import crypto from 'crypto';

const prisma = new PrismaClient();

export async function seedDatabase() {
  // Check if the database already has data.
  const existingUsers = await prisma.user.findMany();
  if (existingUsers.length > 0) {
    console.log("Database already seeded. Skipping seeding.");
    return;
  }

  // Seed users.
  const seedUsers = [
    { email: "tarnished@arcane.htb", username: "Tarnished", password: crypto.randomBytes(8).toString('hex') },
    { email: "melina@arcane.htb", username: "Melina", password: crypto.randomBytes(8).toString('hex') },
    { email: "ranni@arcane.htb", username: "Ranni", password: crypto.randomBytes(8).toString('hex') },
    { email: "blaidd@arcane.htb", username: "Blaidd", password: crypto.randomBytes(8).toString('hex') },
    { email: "fia@arcane.htb", username: "Fia", password: crypto.randomBytes(8).toString('hex') }
  ];
  await prisma.user.createMany({ data: seedUsers });
  console.log("Users seeded.");

  // Get all users to use for seller assignment.
  const users = await prisma.user.findMany();

  // Seed items.
  const seedItems = [
    {
      id: "1",
      name: "Arcane Blade",
      description: "A shimmering sword imbued with ancient magic from a forgotten era.",
      imageUrl: "/image/elden-greatsword.jpg",
      currentBid: 3000,
      timeLeft: "3h",
      category: "Weapons",
      act: "Dawn of Mysteries",
      element: "Mystic",
      rarity: "Rare",
      magicType: "Elemental",
      faction: "Order of the Luminous",
      origin: "Mystic Vale",
      material: "Enchanted Steel",
      weight: 5.5,
      levelRequirement: 20,
      sellerId: users[0].id // Assign the first user as the seller.
    },
    {
      id: "2",
      name: "Mystic Orb",
      description: "An orb pulsating with enigmatic energy that unveils hidden secrets.",
      imageUrl: "/image/mystic-orb.jpg",
      currentBid: 4500,
      timeLeft: "2h 30m",
      category: "Artifacts",
      act: "Veil of Secrets",
      element: "Arcane",
      rarity: "Epic",
      magicType: "Divine",
      faction: "Seers of the Veil",
      origin: "Celestial Spire",
      material: "Crystal",
      weight: 2.0,
      levelRequirement: 25,
      sellerId: users[0].id
    },
    {
      id: "3",
      name: "Shadow Dagger",
      description: "A swift dagger that melts into darkness with every strike.",
      imageUrl: "/image/shadow-dagger.jpg",
      currentBid: 3200,
      timeLeft: "2h",
      category: "Weapons",
      act: "Nightfall",
      element: "Dark",
      rarity: "Uncommon",
      magicType: "Necromancy",
      faction: "Twilight Assassins",
      origin: "Obsidian City",
      material: "Blackened Steel",
      weight: 1.8,
      levelRequirement: 18,
      sellerId: users[0].id
    },
    {
      id: "4",
      name: "Ancient Tapestry",
      description: "A woven masterpiece that chronicles the rise and fall of long-lost dynasties.",
      imageUrl: "/image/ancient-tapestry.jpg",
      currentBid: 2800,
      timeLeft: "3h 15m",
      category: "Artifacts",
      act: "Dawn of Mysteries",
      element: "Fire",
      rarity: "Rare",
      magicType: "Enchantment",
      faction: "Guardians of Memory",
      origin: "Forgotten Keep",
      material: "Woven Silk",
      weight: 1.0,
      levelRequirement: 15,
      sellerId: users[0].id
    },
    {
      id: "5",
      name: "Celestial Crown",
      description: "A crown crafted from celestial metal, said to grant visions of destiny.",
      imageUrl: "/image/celestial-crown.jpg",
      currentBid: 5000,
      timeLeft: "2h 45m",
      category: "Artifacts",
      act: "Celestial Omen",
      element: "Light",
      rarity: "Legendary",
      magicType: "Divine",
      faction: "Order of the Luminous",
      origin: "Skyreach",
      material: "Celestial Gold",
      weight: 2.2,
      levelRequirement: 30,
      sellerId: users[0].id
    },
    {
      id: "6",
      name: "Enchanted Gauntlets",
      description: "Gauntlets that channel ancient power, increasing strength beyond mortal limits.",
      imageUrl: "/image/enchanted-gauntlets.jpg",
      currentBid: 4000,
      timeLeft: "3h 10m",
      category: "Armor",
      act: "Dawn of Mysteries",
      element: "Earth",
      rarity: "Uncommon",
      magicType: "Enchantment",
      faction: "Guardians of Memory",
      origin: "Stonehaven",
      material: "Runed Iron",
      weight: 6.0,
      levelRequirement: 22,
      sellerId: users[0].id
    }
  ];
  await prisma.item.createMany({ data: seedItems });
  console.log("Items seeded.");

  // For each item, seed bids only if none exist.
  const items = await prisma.item.findMany();
  for (const item of items) {
    const existingBids = await prisma.bid.findMany({ where: { itemId: item.id } });
    if (existingBids.length === 0) {
      const randomUsers = users.sort(() => 0.5 - Math.random()).slice(0, 2);
      const bid1Amount = item.currentBid + 500;
      const bid2Amount = item.currentBid + 1000;
      await prisma.bid.create({
        data: {
          amount: bid1Amount,
          item: { connect: { id: item.id } },
          user: { connect: { id: randomUsers[0].id } }
        }
      });
      await prisma.bid.create({
        data: {
          amount: bid2Amount,
          item: { connect: { id: item.id } },
          user: { connect: { id: randomUsers[1].id } }
        }
      });
      await prisma.item.update({
        where: { id: item.id },
        data: { currentBid: bid2Amount }
      });
    }
  }
  console.log("Bids seeded.");
}

export default prisma;
